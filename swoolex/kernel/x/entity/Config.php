<?php
/**
 * +----------------------------------------------------------------------
 * 配置文件加载
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;
use design\AbstractSingleCase;

class Config
{
    use AbstractSingleCase;
    
    /**
     * 全站配置项
    */
    private static $config = [];

    /**
     * 初始化配置项
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function start() {
        $start_time = explode(' ',microtime());

        $path = ROOT_PATH.'/config/';
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file == '.' || $file == '..') continue;
            $key = str_replace('.php', '', $file);
            self::$config[$key] = require $path.$file;
        }

        \design\StartRecord::config($start_time);
    }

    /**
     * 递归获取多级配置
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param array $config 配置
     * @param mixed $array 读取层级
     * @return void
    */
    private static function loop_get($config, $array) {
        foreach ($array as $k=>$v) {
            if (!isset($array[$k+1])) {
                return $config[$v] ?? false;
            } else {
                unset($array[$k]);
                if (!isset($config[$v])) return false;
                
                return self::loop_get($config[$v], $array);
            }
        }
        return false;
    }

    /**
     * 递归设置多级配置
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 设置层级
     * @param mixed $val 设置内容
     * @param array $list 最终组装出来的配置项
     * @return array|false
    */
    private static function loop_set($array, $val) {
        $list = [];
        foreach ($array as $k=>$v) {
            if (!isset($array[$k+1])) {
                $list[$v] = $val;
            } else {
                unset($array[$k]);
                $list[$v] = self::loop_set($array, $val);
            }
            return $list;
        } 

        return false;
    }

    /**
     * 获取参数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 读取配置，递归使用.区分，空的时候读取全部
     * @return mixed
    */
    public function get($key=null) {
        if (is_null($key)) {
            return self::$config;
        } else if (strpos($key, '.') !== false) {
            return self::loop_get(self::$config, explode('.', $key));
        } else if (isset(self::$config[$key])) {
            return self::$config[$key];
        }

        return false;
    }

    /**
     * 设置参数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 配置位置，递归使用.区分
     * @param mixed $val 配置参数
     * @return bool
    */
    public function set($key, $val) {
        $array = explode('.', $key);
        if (!isset($array[1])) {
            $keys = $array[0];
            self::$config[$keys] = $val;
            unset($keys);
            unset($array);
            return true;
        }

        $list = self::loop_set($array, $val);
        if ($list) {
            foreach ($list as $key=>$val) {
                $config = array_merge(self::$config[$key], $val);
                self::$config[$key] = $config;
                unset($keys);
                unset($array);
                unset($config);
                return true;
            }
        }

        return false;
    }

    /**
     * 判断参数是否存在
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 读取配置，递归使用.区分，空的时候读取全部
     * @return bool
    */
    public function has($key) {
        if (strpos($key, '.') !== false) {
            return self::loop_get(self::$config, explode('.', $key));
        } else if (isset(self::$config[$key])) {
            return true;
        }

        return false;
    }
}
