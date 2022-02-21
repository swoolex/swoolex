<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 5版本 客户端接包数据转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/simps/mqtt
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\v5;

use x\mqtt\common\HexCodes;
use x\mqtt\common\dc\UnDataConv;
use x\mqtt\common\Types;
use x\mqtt\common\tool\UnPack as UnStream;

class UnPack {
    public static function connect($remaining) {
        $protocolName = UnStream::string($remaining);
        $protocolLevel = ord($remaining[0]);
        $cleanSession = ord($remaining[1]) >> 1 & 0x1;
        $willFlag = ord($remaining[1]) >> 2 & 0x1;
        $willQos = ord($remaining[1]) >> 3 & 0x3;
        $willRetain = ord($remaining[1]) >> 5 & 0x1;
        $passwordFlag = ord($remaining[1]) >> 6 & 0x1;
        $userNameFlag = ord($remaining[1]) >> 7 & 0x1;
        $remaining = substr($remaining, 2);
        $keepAlive = UnStream::shortInt($remaining);
        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $properties = UnDataConv::connect($propertiesTotalLength, $remaining);
        }
        $clientId = UnStream::string($remaining);
        if ($willFlag) {
            $willPropertiesTotalLength = UnStream::byte($remaining);
            if ($willPropertiesTotalLength) {
                $willProperties = UnDataConv::willProperties($willPropertiesTotalLength, $remaining);
            }
            $willTopic = UnStream::string($remaining);
            $willMessage = UnStream::string($remaining);
        }
        $userName = $password = '';
        if ($userNameFlag) {
            $userName = UnStream::string($remaining);
        }
        if ($passwordFlag) {
            $password = UnStream::string($remaining);
        }
        $package = [
            'type' => Types::CONNECT,
            'protocol_name' => $protocolName,
            'protocol_level' => $protocolLevel,
            'clean_session' => $cleanSession,
            'properties' => [],
            'will' => [],
            'user_name' => $userName,
            'password' => $password,
            'keep_alive' => $keepAlive,
        ];

        if ($propertiesTotalLength) {
            $package['properties'] = $properties;
        } else {
            unset($package['properties']);
        }

        $package['client_id'] = $clientId;

        if ($willFlag) {
            if ($willPropertiesTotalLength) {
                $package['will']['properties'] = $willProperties;
            }
            $package['will'] += [
                'qos' => $willQos,
                'retain' => $willRetain,
                'topic' => $willTopic,
                'message' => $willMessage,
            ];
        } else {
            unset($package['will']);
        }

        return $package;
    }

    public static function connAck($remaining) {
        $sessionPresent = ord($remaining[0]) & 0x01;
        $code = ord($remaining[1]);
        $remaining = substr($remaining, 2);

        $package = [
            'type' => Types::CONNACK,
            'session_present' => $sessionPresent,
            'code' => $code,
        ];

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::connAck($propertiesTotalLength, $remaining);
        }

        return $package;
    }

    public static function publish($dup, $qos, $retain, $remaining) {
        $topic = UnStream::string($remaining);

        $package = [
            'type' => Types::PUBLISH,
            'dup' => $dup,
            'qos' => $qos,
            'retain' => $retain,
            'topic' => $topic,
        ];

        if ($qos) {
            $package['message_id'] = UnStream::shortInt($remaining);
        }

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::publish($propertiesTotalLength, $remaining);
        }

        $package['message'] = $remaining;

        return $package;
    }

    public static function subscribe($remaining) {
        $messageId = UnStream::shortInt($remaining);

        $package = [
            'type' => Types::SUBSCRIBE,
            'message_id' => $messageId,
        ];

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::subscribe($propertiesTotalLength, $remaining);
        }

        $topics = [];
        while ($remaining) {
            $topic = UnStream::string($remaining);
            $topics[$topic] = [
                'qos' => ord($remaining[0]) & 0x3,
                'no_local' => (bool) (ord($remaining[0]) >> 2 & 0x1),
                'retain_as_published' => (bool) (ord($remaining[0]) >> 3 & 0x1),
                'retain_handling' => ord($remaining[0]) >> 4,
            ];
            $remaining = substr($remaining, 1);
        }

        $package['topics'] = $topics;

        return $package;
    }

    public static function subAck($remaining) {
        $messageId = UnStream::shortInt($remaining);

        $package = [
            'type' => Types::SUBACK,
            'message_id' => $messageId,
        ];

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::pubAndSub($propertiesTotalLength, $remaining);
        }

        $codes = unpack('C*', $remaining);
        $package['codes'] = array_values($codes);

        return $package;
    }

    public static function unSubscribe($remaining) {
        $messageId = UnStream::shortInt($remaining);

        $package = [
            'type' => Types::UNSUBSCRIBE,
            'message_id' => $messageId,
        ];

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::unSubscribe($propertiesTotalLength, $remaining);
        }
        $topics = [];
        while ($remaining) {
            $topic = UnStream::string($remaining);
            $topics[] = $topic;
        }

        $package['topics'] = $topics;

        return $package;
    }

    public static function unSubAck($remaining) {
        $messageId = UnStream::shortInt($remaining);

        $package = [
            'type' => Types::UNSUBACK,
            'message_id' => $messageId,
        ];

        $propertiesTotalLength = UnStream::byte($remaining);
        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::pubAndSub($propertiesTotalLength, $remaining);
        }

        $codes = unpack('C*', $remaining);
        $package['codes'] = array_values($codes);

        return $package;
    }

    public static function disconnect($remaining) {
        if (isset($remaining[0])) {
            $code = UnStream::byte($remaining);
        } else {
            $code = HexCodes::NORMAL_DISCONNECTION;
        }
        $package = [
            'type' => Types::DISCONNECT,
            'code' => $code,
        ];

        $propertiesTotalLength = 0;
        if (isset($remaining[0])) {
            $propertiesTotalLength = UnStream::byte($remaining);
        }

        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::disconnect($propertiesTotalLength, $remaining);
        }

        return $package;
    }

    public static function getHexCodes($type, $remaining) {
        $messageId = UnStream::shortInt($remaining);

        if (isset($remaining[0])) {
            $code = UnStream::byte($remaining);
        } else {
            $code = HexCodes::SUCCESS;
        }

        $package = [
            'type' => $type,
            'message_id' => $messageId,
            'code' => $code,
        ];

        $propertiesTotalLength = 0;
        if (isset($remaining[0])) {
            $propertiesTotalLength = UnStream::byte($remaining);
        }

        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::pubAndSub($propertiesTotalLength, $remaining);
        }

        return $package;
    }

    public static function auth($remaining) {
        if (isset($remaining[0])) {
            $code = UnStream::byte($remaining);
        } else {
            $code = HexCodes::SUCCESS;
        }
        $package = [
            'type' => Types::AUTH,
            'code' => $code,
        ];

        $propertiesTotalLength = 0;
        if (isset($remaining[0])) {
            $propertiesTotalLength = UnStream::byte($remaining);
        }

        if ($propertiesTotalLength) {
            $package['properties'] = UnDataConv::auth($propertiesTotalLength, $remaining);
        }

        return $package;
    }
}
