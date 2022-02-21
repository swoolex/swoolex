<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - V5 - 服务端发包时数据编码转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/simps/mqtt
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common\dc;

use x\mqtt\common\HexCodes;
use x\mqtt\common\tool\Pack;

class DataConv {

    public static function connect($data) {
        $length = 0;
        $tmpBody = '';
        $connect = array_flip(PacketFields::$connect);
        foreach ($data as $key => $item) {
            if (isset($connect[$key])) {
                $code = $connect[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                        $length += 5;
                        $tmpBody .= Pack::longInt($item);
                        break;
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                    case HexCodes::REQUEST_PROBLEM_INFORMATION:
                    case HexCodes::REQUEST_RESPONSE_INFORMATION:
                        $length += 2;
                        $tmpBody .= chr((int) $item);
                        break;
                    case HexCodes::RECEIVE_MAXIMUM:
                    case HexCodes::TOPIC_ALIAS_MAXIMUM:
                    case HexCodes::MAXIMUM_PACKET_SIZE:
                        $length += 3;
                        $tmpBody .= Pack::shortInt($item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($connect['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function willProperties($data) {
        $length = 0;
        $tmpBody = '';
        $willProperties = array_flip(PacketFields::$willProperties);
        foreach ($data as $key => $item) {
            if (isset($willProperties[$key])) {
                $code = $willProperties[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::MESSAGE_EXPIRY_INTERVAL:
                    case HexCodes::WILL_DELAY_INTERVAL:
                        $length += 5;
                        $tmpBody .= Pack::longInt($item);
                        break;
                    case HexCodes::CONTENT_TYPE:
                    case HexCodes::RESPONSE_TOPIC:
                    case HexCodes::CORRELATION_DATA:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                    case HexCodes::PAYLOAD_FORMAT_INDICATOR:
                        $length += 2;
                        $tmpBody .= chr((int) $item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($willProperties['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function connAck($data) {
        $length = 0;
        $tmpBody = '';
        $connAck = array_flip(PacketFields::$connAck);
        foreach ($data as $key => $item) {
            if (isset($connAck[$key])) {
                $code = $connAck[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                    case HexCodes::MAXIMUM_PACKET_SIZE:
                        $length += 5;
                        $tmpBody .= Pack::longInt($item);
                        break;
                    case HexCodes::SERVER_KEEP_ALIVE:
                    case HexCodes::RECEIVE_MAXIMUM:
                    case HexCodes::TOPIC_ALIAS_MAXIMUM:
                        $length += 3;
                        $tmpBody .= Pack::shortInt($item);
                        break;
                    case HexCodes::ASSIGNED_CLIENT_IDENTIFIER:
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                    case HexCodes::RESPONSE_INFORMATION:
                    case HexCodes::SERVER_REFERENCE:
                    case HexCodes::REASON_STRING:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                    case HexCodes::MAXIMUM_QOS:
                    case HexCodes::RETAIN_AVAILABLE:
                    case HexCodes::WILDCARD_SUBSCRIPTION_AVAILABLE:
                    case HexCodes::SUBSCRIPTION_IDENTIFIER_AVAILABLE:
                    case HexCodes::SHARED_SUBSCRIPTION_AVAILABLE:
                        $length += 2;
                        $tmpBody .= chr((int) $item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($connAck['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function publish($data) {
        $length = 0;
        $tmpBody = '';
        $publish = array_flip(PacketFields::$publish);
        foreach ($data as $key => $item) {
            if (isset($publish[$key])) {
                $code = $publish[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::MESSAGE_EXPIRY_INTERVAL:
                        $length += 5;
                        $tmpBody .= Pack::longInt($item);
                        break;
                    case HexCodes::TOPIC_ALIAS:
                        $length += 3;
                        $tmpBody .= Pack::shortInt($item);
                        break;
                    case HexCodes::CONTENT_TYPE:
                    case HexCodes::RESPONSE_TOPIC:
                    case HexCodes::CORRELATION_DATA:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                    case HexCodes::PAYLOAD_FORMAT_INDICATOR:
                        $length += 2;
                        $tmpBody .= chr((int) $item);
                        break;
                    case HexCodes::SUBSCRIPTION_IDENTIFIER:
                        $length += 1;
                        $value = Pack::varInt((int) $item);
                        $length += strlen($value);
                        $tmpBody .= $value;
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($publish['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function pubAndSub($data) {
        $length = 0;
        $tmpBody = '';
        $pubAndSub = array_flip(PacketFields::$pubAndSub);
        foreach ($data as $key => $item) {
            if (isset($pubAndSub[$key])) {
                $code = $pubAndSub[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::REASON_STRING:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($pubAndSub['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function subscribe($data) {
        $length = 0;
        $tmpBody = '';
        $subscribe = array_flip(PacketFields::$subscribe);
        foreach ($data as $key => $item) {
            if (isset($subscribe[$key])) {
                $code = $subscribe[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::SUBSCRIPTION_IDENTIFIER:
                        $length += 1;
                        $value = Pack::varInt((int) $item);
                        $length += strlen($value);
                        $tmpBody .= $value;
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($subscribe['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function unSubscribe($data) {
        $length = 0;
        $tmpBody = '';
        $unSubscribe = array_flip(PacketFields::$unSubscribe);
        foreach ($data as $key => $item) {
            $length += 5;
            $length += strlen((string) $key);
            $length += strlen((string) $item);
            $tmpBody .= chr($unSubscribe['user_property']);
            $tmpBody .= Pack::stringPair((string) $key, (string) $item);
        }

        return chr($length) . $tmpBody;
    }

    public static function disConnect($data) {
        $length = 0;
        $tmpBody = '';
        $disConnect = array_flip(PacketFields::$disConnect);
        foreach ($data as $key => $item) {
            if (isset($disConnect[$key])) {
                $code = $disConnect[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                        $length += 5;
                        $tmpBody .= Pack::longInt($item);
                        break;
                    case HexCodes::SERVER_REFERENCE:
                    case HexCodes::REASON_STRING:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($disConnect['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }

    public static function auth($data) {
        $length = 0;
        $tmpBody = '';
        $auth = array_flip(PacketFields::$auth);
        foreach ($data as $key => $item) {
            if (isset($auth[$key])) {
                $code = $auth[$key];
                $tmpBody .= chr($code);
                switch ($code) {
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                    case HexCodes::REASON_STRING:
                        $length += 3;
                        $length += strlen($item);
                        $tmpBody .= Pack::string($item);
                        break;
                }
            } else {
                $length += 5;
                $length += strlen((string) $key);
                $length += strlen((string) $item);
                $tmpBody .= chr($auth['user_property']);
                $tmpBody .= Pack::stringPair((string) $key, (string) $item);
            }
        }

        return chr($length) . $tmpBody;
    }
}
