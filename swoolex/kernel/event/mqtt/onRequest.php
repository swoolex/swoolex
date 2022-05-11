<?php
/**
 * +----------------------------------------------------------------------
 * 监听外部调用请求
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\mqtt;

class onRequest {
    /**
	 * 启动实例
	*/
    public $server;
    /**
	 * 配置项
	*/
    public $config;

    /**
     * 接收服务实例
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param array $config 配置项
     * @param Swoole $server
    */
    public function __construct($server, $config) {
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @param Swoole\Http\Request $request HTTP请求对象
     * @param Swoole\Http\Response $response HTTP响应对象
    */
    public function run($request, $response) {
        // 调用二次转发，不做重载
        $on = new \box\event\server\onRequest($this->server, $this->config);
        $on->run();
    }
}

