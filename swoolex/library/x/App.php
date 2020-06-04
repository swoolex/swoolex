<?php
// +----------------------------------------------------------------------
// | 应用启动类-单例-只允许被调用一次
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class App
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 需要启动的服务类型
    */
    private static $service = null;
    /**
     * Swoole服务端的set项
    */
    private static $set = [];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return App
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new App();
            \x\StartEo::run(\x\Lang::run()->get('start -5'));
            return self::$instance;
        }

        \x\StartEo::run("App：Can only be started once~", 'error');
    }

    /**
     * 注入需要启动的服务类型
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $type
     * @return void
    */
    public function service($type) {
        self::$service = strtolower($type);
        return $this;
    }

    /**
     * 注入Swoole的配置项
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $set
     * @return void
    */
    public function set($set) {
        self::$set = $set;
        return $this;
    }

    /**
     * 启动服务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function start() {
        if (
            self::$service != 'http' && 
            self::$service != 'server' && 
            self::$service != 'websocket'
        ) {
            \x\StartEo::run("App：Start only allowed Http、Server、WebSocekt Type of service~", 'error');
        }
        \x\StartEo::run("App：Type = ".self::$service);

        switch (self::$service) {
            case 'http': 
                // 先初始化路由表
                \x\route\Table::run()->start();
                $service = new \x\service\http\Http(); 
            break;
            case 'server': 
                $service = new \x\service\server\Server();
            break;
            case 'websocket': 
                // 先初始化socket路由表
                \x\route\WebSocket::run()->start();
                $service = new \x\service\websocket\WebSocket();
            break;
        }
        
        $service->start(self::$set);
    }
}