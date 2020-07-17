<?php
// +----------------------------------------------------------------------
// | 监听客户端消息发送请求
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onMessage
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\WebSocket $server
     * @param Swoole\WebSocket\Frames $frame 状态信息
     * @return void
    */
    public function run($server, $frame) {
        $this->server = $server;
        
        // 错误和异常处理注入参数
        \x\Error::run()->set('websocket', $server, $frame);

        # 开始转发路由
        $obj = new \x\Route(null, null, $server, $frame);
        $obj->start();

        // 调用二次转发，不做重载
        $on = new \app\event\onMessage;
        $on->run($server, $frame);
    }
}

