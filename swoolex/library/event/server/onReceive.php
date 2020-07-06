<?php
// +----------------------------------------------------------------------
// | 接收到数据时
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\server;

class onReceive
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.3 + 2020.07.06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @return void
    */
    public function run($server, $fd, $reactorId, $data=null) {
        $this->server = $server;
        
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \app\event\server\onReceive;
        $on->run($server, $fd, $reactorId, $data);
    }

}

