<?php
/**
 * +----------------------------------------------------------------------
 * 语言包
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;

class Lang {
    private static $instance = null;
    private function __construct(){}
    private function __clone(){} 
    /**
     * 使用的语言包
    */
    private static $config = [];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @param string $lang 可手动指定语言包文件
     * @return Lang
    */
    public static function run($lang=null){
        if (empty(self::$instance)) {
            self::$instance = new static();
            self::$instance::runtime($lang);
        }
        return self::$instance;
    }

    /**
     * 初始化配置项
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @param string $lang 可手动指定语言包文件
    */
    private static function runtime($lang) {
        if (!$lang) {
            $lang = \x\Config::get('app.lang');
        }
        
        $path = ROOT_PATH.'/lang/'.$lang.'.php';
        if (!is_file($path)) {
            throw new \Exception("Lang：{$path} No Existent~");
        }

        self::$config = require $path;
    }

    /**
     * 获取参数
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @param string $key 读取的项
     * @return mixed
    */
    public function get($key=null) {
        if (!isset(self::$config[$key])) return false;

        return self::$config[$key];
    }

}
