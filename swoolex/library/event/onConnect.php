<?php
// +----------------------------------------------------------------------
// | 有新的连接进入
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onConnect
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
     * @param Swoole $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @return void
    */
    public function run($server, $fd, $reactorId) {
        try {
            $this->server = $server;

            // 调用二次转发，不做重载
            $on = new \app\event\onConnect;
            $on->run($server, $fd, $reactorId);
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }

}

