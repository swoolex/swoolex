<?php
// +----------------------------------------------------------------------
// | 语言包-单例
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\entity;

class Lang
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 使用的语言包
    */
    private static $config = [];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param string $lang 可手动指定语言包文件
     * @return Lang
    */
    public static function run($lang=null){
        if (empty(self::$instance)) {
            self::$instance = new \x\entity\Lang();
            self::$instance::runtime($lang);
        }
        return self::$instance;
    }

    /**
     * 初始化配置项
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param string $lang 可手动指定语言包文件
     * @return void
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 读取的项
     * @return mixed
    */
    public function get($key=null) {
        if (!isset(self::$config[$key])) return false;

        return self::$config[$key];
    }

}
