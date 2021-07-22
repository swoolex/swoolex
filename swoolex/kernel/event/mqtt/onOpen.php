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

namespace event\mqtt;

class onOpen {
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
     * @param Swoole\Http\Request $request HTTP请求对象
     * @return void
    */
    public function run($server, $request) {
        $this->server = $server;
        // 调用二次转发，不做重载
        $on = new \box\event\server\onOpen;
        $on->run($server, $request);
    }
}

