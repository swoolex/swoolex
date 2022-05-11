<?php
/**
 * +----------------------------------------------------------------------
 * 接收到 UDP 数据包时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\websocket;

class onPacket
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param Swoole $server
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @param array $clientInfo 客户端信息包括 address/port/server_socket 等多项客户端信息数据
    */
    public function run($server, $data, $clientInfo) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \box\event\server\onPacket;
        $on->run($server, $data, $clientInfo);
    }

    
}

