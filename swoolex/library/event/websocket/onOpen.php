<?php
// +----------------------------------------------------------------------
// | 监听WebSocket握手成功
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\websocket;

class onOpen
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\WebSocket\Server $server
     * @param Swoole\Http\Request $request HTTP请求对象
     * @return void
    */
    public function run($server, $request) {
        $this->server = $server;
        // 调用二次转发，不做重载
        $on = new \app\event\websocket\onOpen;
        $on->run($server, $request);
    }
}

