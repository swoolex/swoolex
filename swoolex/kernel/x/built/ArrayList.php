<?php
/**
 * +----------------------------------------------------------------------
 * 数组常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\built;

class ArrayList
{
    /**
     * 二维数组 指定某列转为字符串
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 数组
     * @param string $field 字段名
     * @param string $spacer 分隔符
     * @return array|string
    */
    public static function toString($array, $field, $spacer=',') {
        $ret = '';
        foreach ($array as $v) {
            if (isset($v[$field])) {
                if ($ret) $ret .= ',';
                $ret .= $v[$field];
            }
        }
        return $ret;
    }
    /**
     * 二维数组 指定某列转为一维数组
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 数组
     * @param string $field 字段名
     * @return array|string
    */
    public static function toOne($array, $field) {
        $ret = [];
        foreach ($array as $v) {
            if (isset($v[$field])) {
                $ret[] = $v[$field];
            }
        }
        return $ret;
    }
    
    /**
     * 多维数组合并
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param $array1 数组一
     * @param $array2 数组二
     * @return array
    */
    public static function mergeMultiple($array1, $array2) {
        $merge = $array1 + $array2;
        $data = [];
        foreach ($merge as $key => $val) {
            if (
                isset($array1[$key])
                && is_array($array1[$key])
                && isset($array2[$key])
                && is_array($array2[$key])
            ) {
                $data[$key] = self::mergeMultiple($array1[$key], $array2[$key]);
            } else {
                $data[$key] = isset($array2[$key]) ? $array2[$key] : $array1[$key];
            }
        }
        return $data;
    }

    /**
     * 二维数组排序
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $arr 排序对象
     * @param string $keys 排序字段
     * @param bool $desc 是否降序
     * @return array
    */
    public static function twoSort($arr, $keys, $desc = false) {
        $key_value = $new_array = [];
        foreach ($arr as $k => $v) {
            $key_value[$k] = $v[$keys];
        }
        if ($desc) {
            arsort($key_value);
        } else {
            asort($key_value);
        }
        reset($key_value);
        foreach ($key_value as $k => $v) {
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    /**
     * 二维数组生成递归结构
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 递归对象
     * @param string $field 父字段名
     * @param string $menu 生成递归结构的字段名
     * @param int $pid 默认的父字段值
     * @return void
    */
    public static function recursion($array='', $field='pid', $menu = 'list', $pid = 0) {
        $arr = [];
        foreach ($array as $v) {
            if ($v[$field] == $pid) {
                $v[$menu] = self::recursion($array, $field, $menu, $v['id']);
                $arr[] = $v;
            }
        }
        return $arr;
    }

    /**
     * 比较数组是否相等
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $arr1 数组一
     * @param array $arr2 数组二
     * @param bool $nocase 是否不区分大小写
     * @return bool
    */
    public static function equal($arr1, $arr2, $nocase=true){
        if (count($arr1) != count($arr2)) return false;

        $arrStr1 = serialize($arr1);
        $arrStr2 = serialize($arr2);

        if ($nocase) {
            $res = strcasecmp($arrStr1, $arrStr2);
        } else {
            $res = strcmp($arrStr1, $arrStr2);
        }

        if ( $res==0 ) return true;
        return false;
    }

    /**
     * 获取数组结构深度
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 数组对象
     * @return int
    */
    public static function level($array){
        if(!is_array($array)) return 0;
        $max_depth = 1;
        foreach ($array as $value) {
            if (is_array($value)) {
                $max_depth = self::level($value) + 1;
                break;
            }
        }
        return $max_depth;
    }
}