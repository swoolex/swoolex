<?php
/**
 * +----------------------------------------------------------------------
 * 数据类型判断
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

class Type {
    // 字符串
    public static function string($param) {
        return is_string($param);
    }
    // 整型
    public static function int($param) {
        return is_int($param);
    }
    // 浮点数
    public static function float($param) {
        return is_float($param);
    }
    // 布尔值
    public static function bool($param) {
        return is_bool($param);
    }
    // 数组
    public static function array($param) {
        return is_array($param);
    }
    // NULL
    public static function null($param) {
        return is_null($param);
    }
    // 对象
    public static function class($param) {
        return is_object($param);
    }
    // 闭包
    public static function closure($param) {
        return ($param instanceof Closure);
    }
    // 回调
    public static function callback($param) {
        return is_callable($param);
    }
    // 资源
    public static function resource($param) {
        return is_resource($param);
    }
    // 标量
    public static function scalar($param) {
        return is_scalar($param);
    }
    // 数字
    public static function numeric($param) {
        return is_numeric($param);
    }
    // 判断是否为真空
    public static function empty($param) {
        if (self::numeric($param) === true) return false;
        
        return empty($param);
    }
    // 多个类型兼容
    public static function all($param, $array) {
        foreach ($array as $type) {
            $type = strtolower($type);
            if (self::$type($param) === true) return true;
        }

        return false;
    }
}