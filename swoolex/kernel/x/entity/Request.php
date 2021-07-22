<?php
/**
 * +----------------------------------------------------------------------
 * HTTP请求对象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;

class Request {
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}

    /**
     * 请求实例
    */
    private $request;

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return Request
    */
    public static function run(){
        if (empty(self::$instance)) {
            var_dump('Request');
            self::$instance = new \x\entity\Request();
        }
        
        return self::$instance;
    }

    public function __destruct() {
        var_dump(123);
    }

    /**
     * 设置实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @return void
    */
    public function set($request) {
        $this->request = $request;
    }

    /**
     * 获取实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return Swoole\Http\Request
    */
    public function get() {
        return $this->request;
    }
}