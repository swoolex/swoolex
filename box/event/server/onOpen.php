<?php
/**
 * +----------------------------------------------------------------------
 * 监听WebSocket握手成功
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\event\server;

class onOpen
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @param Swoole\WebSocket\Server $server
     * @param Swoole\Http\Request $request HTTP请求对象
    */
    public function run($server, $request) {
        $this->server = $server;
    }
}

