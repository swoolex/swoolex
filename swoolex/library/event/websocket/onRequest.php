<?php
// +----------------------------------------------------------------------
// | 监听外部调用请求
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\websocket;

class onRequest
{
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $config 配置项
     * @param Swoole\Server $server
     * @return void
    */
    public function __construct($server, $config) {
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @param Swoole\Http\Response $response HTTP响应对象
     * @return void
    */
    public function run($request, $response) {
        // 跨域配置设置
        if ($this->config['origin']) $response->header('Access-Control-Allow-Origin', $this->config['origin']); 
        if ($this->config['type']) $response->header('Content-Type', $this->config['type']); 
        if ($this->config['methods']) $response->header('Access-Control-Allow-Methods', $this->config['methods']); 
        if ($this->config['credentials']) $response->header('Access-Control-Allow-Credentials', $this->config['credentials']); 
        if ($this->config['headers']) $response->header('Access-Control-Allow-Headers', $this->config['headers']); 
        
        # 防止Chrome的空包
        $uri = ltrim($request->server['request_uri'], '/');
        if ($uri == 'favicon.ico') {
            $response->status(404);
            return $response->end();
        }

        # 开始转发路由
        $obj = new \x\Route($request, $response);
        $obj->start();

        // 调用二次转发，不做重载
        $on = new \app\event\websocket\onRequest($this->server, $this->config);
        $on->run($request, $response);
    }
}

