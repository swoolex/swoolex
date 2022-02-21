<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 3版本 服务端发包数据转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/simps/mqtt
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\v3;

use x\mqtt\base\Protocol;
use x\mqtt\common\Types;
use x\mqtt\common\tool\Pack as TypeConv;

class Pack {

    /**
     * 建立连接时
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $array
     * @return void
    */
    public static function connect($array) {
        $body = TypeConv::string($array['protocol_name']) . chr($array['protocol_level']);
        $connectFlags = 0;
        if (!empty($array['clean_session'])) {
            $connectFlags |= 1 << 1;
        }
        if (!empty($array['will'])) {
            $connectFlags |= 1 << 2;
            if (isset($array['will']['qos'])) {
                if ($array['will']['qos'] > Protocol::MQTT_QOS_2) {
                    throw new \Exception("QoS {$array['will']['qos']} not supported");
                }
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

        $body .= TypeConv::string($array['client_id']);
        if (!empty($array['will'])) {
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
        $head = TypeConv::packHeader(Types::CONNACK, strlen($body));

        return $head . $body;
    }

    public static function publish($array) {
        $body = TypeConv::string($array['topic']);
        $qos = $array['qos'] ?? 0;
        if ($qos) {
            $body .= TypeConv::shortInt($array['message_id']);
        }
        $body .= $array['message'];
        $dup = $array['dup'] ?? 0;
        $retain = $array['retain'] ?? 0;
        $head = TypeConv::packHeader(Types::PUBLISH, strlen($body), $dup, $qos, $retain);

        return $head . $body;
    }

    public static function subscribe($array) {
        $id = $array['message_id'];
        $body = TypeConv::shortInt($id);
        foreach ($array['topics'] as $topic => $qos) {
            $body .= TypeConv::string($topic);
            $body .= chr($qos);
        }
        $head = TypeConv::packHeader(Types::SUBSCRIBE, strlen($body), 0, 1);

        return $head . $body;
    }

    public static function subAck($array) {
        $body = TypeConv::shortInt($array['message_id']) . call_user_func_array(
            'pack',
            array_merge(['C*'], $array['codes'])
        );
        $head = TypeConv::packHeader(Types::SUBACK, strlen($body));

        return $head . $body;
    }

    public static function unSubscribe($array) {
        $body = TypeConv::shortInt($array['message_id']);
        foreach ($array['topics'] as $topic) {
            $body .= TypeConv::string($topic);
        }
        $head = TypeConv::packHeader(Types::UNSUBSCRIBE, strlen($body), 0, 1);

        return $head . $body;
    }
}
