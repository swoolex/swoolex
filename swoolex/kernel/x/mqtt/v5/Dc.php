<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - V5 - 数据包处理
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\v5;

use x\mqtt\base\Protocol;
use x\mqtt\common\tool\Pack as Stream;
use x\mqtt\common\tool\UnPack as UnStream;
use x\mqtt\common\Types;

class Dc implements Protocol {

    /**
     * 回复客户端时数据加密
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param array $array
     * @return void
    */
    public static function pack($array) {
        $type = $array['type'];
        switch ($type) {
            case Types::CONNECT:
                $package = Pack::connect($array);
                break;
            case Types::CONNACK:
                $package = Pack::connAck($array);
                break;
            case Types::PUBLISH:
                $package = Pack::publish($array);
                break;
            case Types::PUBACK:
            case Types::PUBREC:
            case Types::PUBREL:
            case Types::PUBCOMP:
                $package = Pack::genReasonPhrase($array);
                break;
            case Types::SUBSCRIBE:
                $package = Pack::subscribe($array);
                break;
            case Types::SUBACK:
                $package = Pack::subAck($array);
                break;
            case Types::UNSUBSCRIBE:
                $package = Pack::unSubscribe($array);
                break;
            case Types::UNSUBACK:
                $package = Pack::unSubAck($array);
                break;
            case Types::PINGREQ:
            case Types::PINGRESP:
                $package = Stream::packHeader($type, 0);
                break;
            case Types::DISCONNECT:
                $package = Pack::disconnect($array);
                break;
            case Types::AUTH:
                $package = Pack::auth($array);
                break;
            default:
                throw new \Exception('MQTT Type not exist');
        }

        return $package;
    }

    /**
     * 接受客户端数据时解密
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $data
     * @return void
    */
    public static function unpack($data) {
        $type = UnStream::getType($data);
        $remaining = UnStream::getRemaining($data);
        switch ($type) {
            case Types::CONNECT:
                $package = UnPack::connect($remaining);
                break;
            case Types::CONNACK:
                $package = UnPack::connAck($remaining);
                break;
            case Types::PUBLISH:
                $dup = ord($data[0]) >> 3 & 0x1;
                $qos = ord($data[0]) >> 1 & 0x3;
                $retain = ord($data[0]) & 0x1;
                $package = UnPack::publish($dup, $qos, $retain, $remaining);
                break;
            case Types::PUBACK:
            case Types::PUBREC:
            case Types::PUBREL:
            case Types::PUBCOMP:
                $package = UnPack::getReasonCode($type, $remaining);
                break;
            case Types::PINGREQ:
            case Types::PINGRESP:
                $package = ['type' => $type];
                break;
            case Types::DISCONNECT:
                $package = UnPack::disconnect($remaining);
                break;
            case Types::SUBSCRIBE:
                $package = UnPack::subscribe($remaining);
                break;
            case Types::SUBACK:
                $package = UnPack::subAck($remaining);
                break;
            case Types::UNSUBSCRIBE:
                $package = UnPack::unSubscribe($remaining);
                break;
            case Types::UNSUBACK:
                $package = UnPack::unSubAck($remaining);
                break;
            case Types::AUTH:
                $package = UnPack::auth($remaining);
                break;
            default:
                $package = [];
        }

        return $package;
    }
}
