<?php
/**
 * +----------------------------------------------------------------------
 * 接收到数据时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\http;
use x\Config;

class onReceive {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.0.3 + 2020.07.06
     * @param Swoole $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
    */
    public function run($server, $fd, $reactorId, $data=null) {
        $this->server = $server;
            
        // 调用二次转发，不做重载
        $on = new \box\event\server\onReceive;
        $on->run($server, $fd, $reactorId, $data);
    }
}

