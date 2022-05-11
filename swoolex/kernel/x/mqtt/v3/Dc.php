<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - V3 - 数据包处理
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
use x\mqtt\common\tool\Pack as Stream;
use x\mqtt\common\tool\UnPack as UnStream;
use x\mqtt\common\Types;

class Dc implements Protocol {

    /**
     * 回复客户端时数据加密
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @param array $array
     * @return string
    */
    public static function pack($array) {
        try {
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
                case Types::UNSUBACK:
                    $body = Stream::shortInt($array['message_id']);
                    if ($type === Types::PUBREL) {
                        $head = Stream::packHeader($type, strlen($body), 0, 1);
                    } else {
                        $head = Stream::packHeader($type, strlen($body));
                    }
                    $package = $head . $body;
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
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = Stream::packHeader($type, 0);
                    break;
                default:
                    throw new \Exception('MQTT Type not exist');
            }
        } catch (\TypeError $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        } catch (\Throwable $e) {
            throw $e;
        }

        return $package;
    }

    /**
     * 接受客户端数据时解密
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @param string $data
     * @return string
    */
    public static function unpack($data) {
        try {
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
                case Types::UNSUBACK:
                    $package = ['type' => $type, 'message_id' => UnStream::shortInt($remaining)];
                    break;
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = ['type' => $type];
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
                default:
                    $package = [];
            }
        } catch (\TypeError $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        } catch (\Throwable $e) {
            throw $e;
        }

        return $package;
    }
}
