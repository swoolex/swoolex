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
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
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
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param array $config 配置
     * @param mixed $array 读取层级
     * @return bool
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
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param array $array 设置层级
     * @param mixed $val 设置内容
     * @return array|false
    */
    private static function loop_set(&$config, $array, $val) {
        if (count($array) == 1 ){
            $config[array_shift($array)] = $val;
        }else{
            //每次弹出一个元素，并且把新的data传递进去
            self::loop_set($config[array_shift($array)], $array, $val);
        }
        return $config;
    }
    /**
     * 获取参数
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
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
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param string $key 配置位置，递归使用.区分
     * @param mixed $val 配置参数
     * @return bool
    */
    public function set($key, $val) {
        $key_arr = explode( '.' , $key);
        $old_config = self::$config;
        self::$config = self::loop_set($old_config, $key_arr , $val);
        unset($old_config);
        return true;
    }

    /**
     * 判断参数是否存在
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
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
