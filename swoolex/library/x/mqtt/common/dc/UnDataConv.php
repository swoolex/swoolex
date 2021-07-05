<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - V5 - 服务端解码时数据编码转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common\dc;

use x\mqtt\common\HexCodes;
use x\mqtt\common\tool\UnPack;

class UnDataConv {

    public static function connect($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$connect[$code])) {
                $key = PacktFields::$connect[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                        $properties[$key] = UnPack::longInt($remaining);
                        $length -= 5;
                        break;
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::REQUEST_PROBLEM_INFORMATION:
                    case HexCodes::REQUEST_RESPONSE_INFORMATION:
                        $properties[$key] = UnPack::byte($remaining);
                        $length -= 2;
                        break;
                    case HexCodes::RECEIVE_MAXIMUM:
                    case HexCodes::TOPIC_ALIAS_MAXIMUM:
                    case HexCodes::MAXIMUM_PACKET_SIZE:
                        $properties[$key] = UnPack::shortInt($remaining);
                        $length -= 3;
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function willProperties($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$willProperties[$code])) {
                $key = PacktFields::$willProperties[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::MESSAGE_EXPIRY_INTERVAL:
                    case HexCodes::WILL_DELAY_INTERVAL:
                        $properties[$key] = UnPack::longInt($remaining);
                        $length -= 5;
                        break;
                    case HexCodes::CONTENT_TYPE:
                    case HexCodes::RESPONSE_TOPIC:
                    case HexCodes::CORRELATION_DATA:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::PAYLOAD_FORMAT_INDICATOR:
                        $properties[$key] = UnPack::byte($remaining);
                        $length -= 2;
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function connAck($length, &$remaining){
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$connAck[$code])) {
                $key = PacktFields::$connAck[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                    case HexCodes::MAXIMUM_PACKET_SIZE:
                        $properties[$key] = UnPack::longInt($remaining);
                        $length -= 5;
                        break;
                    case HexCodes::SERVER_KEEP_ALIVE:
                    case HexCodes::RECEIVE_MAXIMUM:
                    case HexCodes::TOPIC_ALIAS_MAXIMUM:
                        $properties[$key] = UnPack::shortInt($remaining);
                        $length -= 3;
                        break;
                    case HexCodes::ASSIGNED_CLIENT_IDENTIFIER:
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                    case HexCodes::RESPONSE_INFORMATION:
                    case HexCodes::SERVER_REFERENCE:
                    case HexCodes::REASON_STRING:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::MAXIMUM_QOS:
                    case HexCodes::RETAIN_AVAILABLE:
                    case HexCodes::WILDCARD_SUBSCRIPTION_AVAILABLE:
                    case HexCodes::SUBSCRIPTION_IDENTIFIER_AVAILABLE:
                    case HexCodes::SHARED_SUBSCRIPTION_AVAILABLE:
                        $properties[$key] = UnPack::byte($remaining);
                        $length -= 2;
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function publish($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$publish[$code])) {
                $key = PacktFields::$publish[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::MESSAGE_EXPIRY_INTERVAL:
                        $properties[$key] = UnPack::longInt($remaining);
                        $length -= 5;
                        break;
                    case HexCodes::TOPIC_ALIAS:
                        $properties[$key] = UnPack::shortInt($remaining);
                        $length -= 3;
                        break;
                    case HexCodes::CONTENT_TYPE:
                    case HexCodes::RESPONSE_TOPIC:
                    case HexCodes::CORRELATION_DATA:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::PAYLOAD_FORMAT_INDICATOR:
                        $properties[$key] = UnPack::byte($remaining);
                        $length -= 2;
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                    case HexCodes::SUBSCRIPTION_IDENTIFIER:
                        $length -= 1;
                        $properties[$key] = UnPack::varInt($remaining, $len);
                        $length -= $len;
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function pubAndSub($length, &$remaining){
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$pubAndSub[$code])) {
                $key = PacktFields::$pubAndSub[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::REASON_STRING:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function subscribe($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$subscribe[$code])) {
                $key = PacktFields::$subscribe[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                    case HexCodes::SUBSCRIPTION_IDENTIFIER:
                        $length -= 1;
                        $properties[$key] = UnPack::varInt($remaining, $len);
                        $length -= $len;
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function unSubscribe($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$unSubscribe[$code])) {
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function disConnect($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$disConnect[$code])) {
                $key = PacktFields::$disConnect[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::SESSION_EXPIRY_INTERVAL:
                        $properties[$key] = UnPack::longInt($remaining);
                        $length -= 5;
                        break;
                    case HexCodes::SERVER_REFERENCE:
                    case HexCodes::REASON_STRING:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }

    public static function auth($length, &$remaining) {
        $properties = [];
        do {
            $code = ord($remaining[0]);
            if (isset(PacktFields::$auth[$code])) {
                $key = PacktFields::$auth[$code];
                $remaining = substr($remaining, 1);
                switch ($code) {
                    case HexCodes::AUTHENTICATION_METHOD:
                    case HexCodes::AUTHENTICATION_DATA:
                    case HexCodes::REASON_STRING:
                        $properties[$key] = UnPack::string($remaining);
                        $length -= 3;
                        $length -= strlen($properties[$key]);
                        break;
                    case HexCodes::USER_PROPERTY:
                        $userKey = UnPack::string($remaining);
                        $userValue = UnPack::string($remaining);
                        $properties[$userKey] = $userValue;
                        $length -= 5;
                        $length -= strlen($userKey);
                        $length -= strlen($userValue);
                        break;
                }
            } else {
                $errType = dechex($code);
                throw new \Exception("HexCodes [0x{$errType}] not exist");
            }
        } while ($length > 0);

        return $properties;
    }
}
