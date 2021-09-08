<?php
/**
 * +----------------------------------------------------------------------
 * 字符串常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Str
{
    /**
     * 字符串包含
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iScontain($cover, $condition, $lower=false) {
        if ($lower == true) {
            if (strpos($cover, $condition) !== false) return true;
        } else {
            if (stripos($cover, $condition) !== false) return true;
        }
        return false;
    }

    /**
     * 检查字符串是否以某个字符串开头
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iSstart($cover, $condition, $lower=false) {
        if ($lower == true) {
            if (strpos($cover, $condition) === 0) return true;
        } else {
            if (stripos($cover, $condition) === 0) return true;
        }
        return false;
    }

    /**
     * 检查字符串是否以某个字符串结尾
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iSend($cover, $condition, $lower=false) {
        $length = strlen($cover)-strlen($condition);
        if ($lower == true) {
            if (strrpos($cover, $condition) === $length) return true;
        } else {
            if (strripos($cover, $condition) === $length) return true;
        }
        return false;
    }

    /**
     * 替换字符串第一次出现的位置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $str 条件字符串
     * @param string $condition 目标字符串
     * @param string $cover 替换的字符串
     * @return string
    */
    public static function replaceStart($str, $condition, $cover) {
        return substr_replace($str, $cover, strpos($str, $condition), strlen($condition));
    }

    /**
     * 获取自定长度的随机字符串
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @param int $type 模式 1.纯数字 2.数+小英 3.数+大英 4.混合
     * @return void
    */
    public static function random($length=16, $type=4) {
        switch ($type) {
            case 1: $str = '0123456789'; $num = 9; break;
            case 2: $str = 'a0sqd1fwg2hej3krl4ztx5cyv6bun7mi8o9p'; $num = 35; break;
            case 3: $str = 'A0SQD1FWG2HEJ3KRL4ZTX5CYV6BUN7MI8O9P'; $num = 35; break;
            case 4: $str = 'qAw0eSrQrtDt1yFyWuGi2oHpEaJs3dKfRgLh4jZkTlXz5xCcYvVb6nBmUN7MI8O9P'; $num = 64; break;
            default:
                return false;
            break;
        }
        $ret = '';
        for ($i=0; $i < $length; $i++) {
            $ret .= $str[mt_rand(0, $num)];
        }
        return $ret;
    }

    /**
     * 获取好看的验证码
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @return int
    */
    public static function smsCode($length=4) {
        if ($length < 4) return false;
        $str = '0123456789'; 
        $num = 9;
        
        // 0.前 1.中 2.后
        $status = mt_rand(0, 2);
        // 双倍数
        $max = $length-3;
        $ret = '';
        for ($i=0; $i < $length; $i++) {
            $ret .= $str[mt_rand(0, $num)];
        }
        // 双倍数替换
        for ($i=0; $i<$max; $i++) {
            $k = $str[mt_rand(0, $num)];
            if ($status == 0) {
                $ret = substr_replace($ret, ($k.$k), 0, 2);
            } else if ($status == 1) {
                $ret = substr_replace($ret, ($k.$k), mt_rand(2, ($length-2)), 2);
            } else if ($status == 2) {
                $ret = substr_replace($ret, ($k.$k), -2, 2);
            }
        }

        return $ret;
    }
}