<?php
/**
 * +----------------------------------------------------------------------
 * 在 (Worker) 进程重启前触发
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\http;

class onWorkerExit {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param Swoole $server
     * @param int $workerId 进程ID
    */
    public function run($server, $workerId) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \box\event\server\onWorkerExit;
        $on->run($server, $workerId);
    }

}

