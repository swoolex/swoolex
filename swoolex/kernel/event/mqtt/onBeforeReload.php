<?php
/**
 * +----------------------------------------------------------------------
 * Worker进程重载前
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\mqtt;

class onBeforeReload {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param Swoole $server
    */
    public function run($server) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \box\event\server\onBeforeReload;
        $on->run($server);
    }
}

