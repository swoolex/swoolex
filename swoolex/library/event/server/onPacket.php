<?php
// +----------------------------------------------------------------------
// | 接收到 UDP 数据包时
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\server;

class onPacket
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
     * @param Swoole\Server $server
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @param array $clientInfo 客户端信息包括 address/port/server_socket 等多项客户端信息数据
     * @return void
    */
    public function run($server, $data, $clientInfo) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \app\event\server\onPacket;
        $on->run($server, $data, $clientInfo);
    }

    
}

