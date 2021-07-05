<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 5版本 服务端发包数据转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\v5;

use x\mqtt\common\HexCodes;
use x\mqtt\common\dc\DataConv;
use x\mqtt\common\Types;
use x\mqtt\common\tool\Pack as TypeConv;

class Pack {
    public static function connect($array) {
        $body = TypeConv::string($array['protocol_name']) . chr($array['protocol_level']);
        $connectFlags = 0;
        if (!empty($array['clean_session'])) {
            $connectFlags |= 1 << 1;
        }
        if (!empty($array['will'])) {
            $connectFlags |= 1 << 2;
            if (isset($array['will']['qos'])) {
                $connectFlags |= $array['will']['qos'] << 3;
            }
            if (!empty($array['will']['retain'])) {
                $connectFlags |= 1 << 5;
            }
        }
        if (!empty($array['password'])) {
            $connectFlags |= 1 << 6;
        }
        if (!empty($array['user_name'])) {
            $connectFlags |= 1 << 7;
        }
        $body .= chr($connectFlags);

        $keepAlive = !empty($array['keep_alive']) && (int) $array['keep_alive'] >= 0 ? (int) $array['keep_alive'] : 0;
        $body .= TypeConv::shortInt($keepAlive);

        $body .= DataConv::connect($array['properties'] ?? []);

        $body .= TypeConv::string($array['client_id']);
        if (!empty($array['will'])) {
            $body .= DataConv::willProperties($array['will']['properties'] ?? []);

            $body .= TypeConv::string($array['will']['topic']);
            $body .= TypeConv::string($array['will']['message']);
        }
        if (!empty($array['user_name'])) {
            $body .= TypeConv::string($array['user_name']);
        }
        if (!empty($array['password'])) {
            $body .= TypeConv::string($array['password']);
        }
        $head = TypeConv::packHeader(Types::CONNECT, strlen($body));

        return $head . $body;
    }

    public static function connAck($array) {
        $body = !empty($array['session_present']) ? chr(1) : chr(0);
        $code = !empty($array['code']) ? $array['code'] : 0;
        $body .= chr($code);

        $body .= DataConv::connAck($array['properties'] ?? []);

        $head = TypeConv::packHeader(Types::CONNACK, strlen($body));

        return $head . $body;
    }

    public static function publish($array) {
        $body = TypeConv::string($array['topic']);
        $qos = $array['qos'] ?? 0;
        if ($qos) {
            $body .= TypeConv::shortInt($array['message_id']);
        }
        $dup = $array['dup'] ?? 0;
        $retain = $array['retain'] ?? 0;

        $body .= DataConv::publish($array['properties'] ?? []);

        $body .= $array['message'];
        $head = TypeConv::packHeader(Types::PUBLISH, strlen($body), $dup, $qos, $retain);

        return $head . $body;
    }

    public static function subscribe($array) {
        $body = TypeConv::shortInt($array['message_id']);

        $body .= DataConv::subscribe($array['properties'] ?? []);

        foreach ($array['topics'] as $topic => $options) {
            $body .= TypeConv::string($topic);

            $subscribeOptions = 0;
            if (isset($options['qos'])) {
                $subscribeOptions |= (int) $options['qos'];
            }
            if (isset($options['no_local'])) {
                $subscribeOptions |= (int) $options['no_local'] << 2;
            }
            if (isset($options['retain_as_published'])) {
                $subscribeOptions |= (int) $options['retain_as_published'] << 3;
            }
            if (isset($options['retain_handling'])) {
                $subscribeOptions |= (int) $options['retain_handling'] << 4;
            }
            $body .= chr($subscribeOptions);
        }

        $head = TypeConv::packHeader(Types::SUBSCRIBE, strlen($body), 0, 1);

        return $head . $body;
    }

    public static function subAck($array) {
        $body = TypeConv::shortInt($array['message_id']);

        $body .= DataConv::pubAndSub($array['properties'] ?? []);

        $body .= call_user_func_array(
            'pack',
            array_merge(['C*'], $array['codes'])
        );
        $head = TypeConv::packHeader(Types::SUBACK, strlen($body));

        return $head . $body;
    }

    public static function unSubscribe($array) {
        $body = TypeConv::shortInt($array['message_id']);

        $body .= DataConv::unSubscribe($array['properties'] ?? []);

        foreach ($array['topics'] as $topic) {
            $body .= TypeConv::string($topic);
        }
        $head = TypeConv::packHeader(Types::UNSUBSCRIBE, strlen($body), 0, 1);

        return $head . $body;
    }

    public static function unSubAck($array) {
        $body = TypeConv::shortInt($array['message_id']);

        $body .= DataConv::pubAndSub($array['properties'] ?? []);

        $body .= call_user_func_array(
            'pack',
            array_merge(['C*'], $array['codes'])
        );
        $head = TypeConv::packHeader(Types::UNSUBACK, strlen($body));

        return $head . $body;
    }

    public static function disconnect($array) {
        $code = !empty($array['code']) ? $array['code'] : HexCodes::NORMAL_DISCONNECTION;
        $body = chr($code);

        $body .= DataConv::disConnect($array['properties'] ?? []);

        $head = TypeConv::packHeader(Types::DISCONNECT, strlen($body));

        return $head . $body;
    }

    public static function genReasonPhrase($array) {
        $body = TypeConv::shortInt($array['message_id']);
        $code = !empty($array['code']) ? $array['code'] : HexCodes::SUCCESS;
        $body .= chr($code);

        $body .= DataConv::pubAndSub($array['properties'] ?? []);

        if ($array['type'] === Types::PUBREL) {
            $head = TypeConv::packHeader($array['type'], strlen($body), 0, 1);
        } else {
            $head = TypeConv::packHeader($array['type'], strlen($body));
        }

        return $head . $body;
    }

    public static function auth($array) {
        $code = !empty($array['code']) ? $array['code'] : HexCodes::SUCCESS;
        $body = chr($code);

        $body .= DataConv::auth($array['properties'] ?? []);

        $head = TypeConv::packHeader(Types::AUTH, strlen($body));

        return $head . $body;
    }
}
