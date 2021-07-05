<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务-服务端数据转换工具
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common\tool;

class Pack {

    public static function string($str) {
        $len = strlen($str);
        return pack('n', $len) . $str;
    }

    public static function stringPair($key, $value) {
        return static::string($key) . static::string($value);
    }

    public static function longInt($int) {
        return pack('N', $int);
    }

    public static function shortInt($int) {
        return pack('n', $int);
    }

    public static function varInt($int) {
        return static::packRemainingLength($int);
    }

    public static function packHeader($type, $bodyLength, $dup = 0, $qos = 0, $retain = 0) {
        $type = $type << 4;
        if ($dup) {
            $type |= 1 << 3;
        }
        if ($qos) {
            $type |= $qos << 1;
        }
        if ($retain) {
            $type |= 1;
        }

        return chr($type) . static::packRemainingLength($bodyLength);
    }

    private static function packRemainingLength($bodyLength) {
        $string = '';
        do {
            $digit = $bodyLength % 128;
            $bodyLength = $bodyLength >> 7;
            if ($bodyLength > 0) {
                $digit = ($digit | 0x80);
            }
            $string .= chr($digit);
        } while ($bodyLength > 0);

        return $string;
    }
}