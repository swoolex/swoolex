<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务-客户端数据转换工具
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\mqtt\common\tool;

class UnPack {

    public static function getType($data) {
        return ord($data[0]) >> 4;
    }

    public static function string(&$remaining) {
        $length = unpack('n', $remaining)[1];
        if ($length + 2 > strlen($remaining)) {
            throw new \Exception("unpack remaining length error, get {$length}");
        }
        $string = substr($remaining, 2, $length);
        $remaining = substr($remaining, $length + 2);

        return $string;
    }

    public static function shortInt(&$remaining) {
        $tmp = unpack('n', $remaining);
        $remaining = substr($remaining, 2);

        return $tmp[1];
    }

    public static function longInt(&$remaining) {
        $tmp = unpack('N', $remaining);
        $remaining = substr($remaining, 4);

        return $tmp[1];
    }

    public static function byte(&$remaining) {
        $tmp = ord($remaining[0]);
        $remaining = substr($remaining, 1);

        return $tmp;
    }

    public static function varInt(&$remaining, &$len) {
        $remainingLength = static::getRemainingLength($remaining, $headBytes);
        $len = $headBytes;

        $result = $shift = 0;
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($remaining[$i]);
            $result |= ($byte & 0x7f) << $shift++ * 7;
        }

        $remaining = substr($remaining, $headBytes, $remainingLength);

        return $result;
    }

    private static function getRemainingLength($data, &$headBytes) {
        $headBytes = $multiplier = 1;
        $value = 0;
        do {
            if (!isset($data[$headBytes])) {
                throw new \Exception('Malformed Remaining Length');
            }
            $digit = ord($data[$headBytes]);
            $value += ($digit & 127) * $multiplier;
            $multiplier *= 128;
            ++$headBytes;
        } while (($digit & 128) != 0);

        return $value;
    }

    public static function getRemaining($data) {
        $remainingLength = static::getRemainingLength($data, $headBytes);

        return substr($data, $headBytes, $remainingLength);
    }

    // 获取协议类型
    public static function getLevel($data) {
        $remaining = static::getRemaining($data);
        $length = unpack('n', $remaining)[1];

        return ord($remaining[$length + 2]);
    }
}
