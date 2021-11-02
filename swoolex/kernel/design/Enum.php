<?php
/**
 * +----------------------------------------------------------------------
 * 枚举基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

class Enum 
{   
    /**
     * 缓存
    */
    private static $cache_list = [];

    /**
     * 读取信息
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $code
     * @param array $data 更多的返回参数
     * @return mixed
    */
    public static function get($code, $data=null) {
        $msg = self::getDoc($code);

        // 组装格式
        if (is_array($data)) {
            return array_merge([
                'code' => $code,
                'msg' => $msg,
            ], $data);
        }

        return $msg;
    }

    /**
     * 反射读取常量注解
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $code
     * @return bool|string
    */
    private static function getDoc($code) {
        $class = get_called_class();
        $obj = new \ReflectionClass(new $class);
        $list = $obj->getConstants();
        $field = false;
        foreach ($list as $k=>$v) {
            if ($v == $code) {
                $field = $k;
                break;
            }
        }
        if (!$field) return false;
        if (isset(self::$cache_list[$class][$field])) {
            return self::$cache_list[$class][$field];
        }
        $content = preg_replace("/[\s]{2,}/","", preg_replace("/\s(?=\s)/","\\1", \Swoole\Coroutine\System::readFile($obj->getFileName()))); 
        $length = stripos($content, '*/ const '.$field.' =');
        $i = $length;
        $doc = false;
        for ($i; $i>=0; $i--) {
            if (substr($content, $i, 2) == '/*') {
                $doc = substr($content, $i+2, $length-$i-2);
                break;
            }
        }
        if (!$doc) return false;
        
        $doc = trim(str_replace('*', '', $doc));

        self::$cache_list[$class][$field] = $doc;
        return $doc;
    }
}