<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 3版本 客户端接包数据转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\v3;

use x\mqtt\common\Types;
use x\mqtt\common\tool\UnPack as UnTypeTool;

class UnPack {
    public static function connect($remaining) {
        $protocolName = UnTypeTool::string($remaining);
        $protocolLevel = ord($remaining[0]);
        $cleanSession = ord($remaining[1]) >> 1 & 0x1;
        $willFlag = ord($remaining[1]) >> 2 & 0x1;
        $willQos = ord($remaining[1]) >> 3 & 0x3;
        $willRetain = ord($remaining[1]) >> 5 & 0x1;
        $passwordFlag = ord($remaining[1]) >> 6 & 0x1;
        $userNameFlag = ord($remaining[1]) >> 7 & 0x1;
        $remaining = substr($remaining, 2);
        $keepAlive = UnTypeTool::shortInt($remaining);
        $clientId = UnTypeTool::string($remaining);
        if ($willFlag) {
            $willTopic = UnTypeTool::string($remaining);
            $willMessage = UnTypeTool::string($remaining);
        }
        $userName = $password = '';
        if ($userNameFlag) {
            $userName = UnTypeTool::string($remaining);
        }
        if ($passwordFlag) {
            $password = UnTypeTool::string($remaining);
        }
        $package = [
            'type' => Types::CONNECT,
            'protocol_name' => $protocolName,
            'protocol_level' => $protocolLevel,
            'clean_session' => $cleanSession,
            'will' => [],
            'user_name' => $userName,
            'password' => $password,
            'keep_alive' => $keepAlive,
            'client_id' => $clientId,
        ];
        if ($willFlag) {
            $package['will'] = [
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
        return ['type' => Types::CONNACK, 'session_present' => ord($remaining[0]) & 0x01, 'code' => ord($remaining[1])];
    }

    public static function publish($dup, $qos, $retain, $remaining) {
        $topic = UnTypeTool::string($remaining);
        if ($qos) {
            $messageId = UnTypeTool::shortInt($remaining);
        }
        $package = [
            'type' => Types::PUBLISH,
            'dup' => $dup,
            'qos' => $qos,
            'retain' => $retain,
            'topic' => $topic,
            'message' => $remaining,
        ];
        if ($qos) {
            $package['message_id'] = $messageId;
        }

        return $package;
    }

    public static function subscribe($remaining) {
        $messageId = UnTypeTool::shortInt($remaining);
        $topics = [];
        while ($remaining) {
            $topic = UnTypeTool::string($remaining);
            $qos = UnTypeTool::byte($remaining);
            $topics[$topic] = $qos;
        }

        return ['type' => Types::SUBSCRIBE, 'message_id' => $messageId, 'topics' => $topics];
    }

    public static function subAck($remaining) {
        $messageId = UnTypeTool::shortInt($remaining);
        $codes = unpack('C*', $remaining);

        return ['type' => Types::SUBACK, 'message_id' => $messageId, 'codes' => array_values($codes)];
    }

    public static function unSubscribe($remaining) {
        $messageId = UnTypeTool::shortInt($remaining);
        $topics = [];
        while ($remaining) {
            $topic = UnTypeTool::string($remaining);
            $topics[] = $topic;
        }

        return ['type' => Types::UNSUBSCRIBE, 'message_id' => $messageId, 'topics' => $topics];
    }
}
