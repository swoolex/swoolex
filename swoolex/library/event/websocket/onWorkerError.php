<?php
// +----------------------------------------------------------------------
// | Worker/Task 进程发生异常后
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\websocket;

class onWorkerError
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
     * @param int $worker_id 异常 worker 进程的 id
     * @param int $worker_pid 异常 worker 进程的 pid
     * @param int $exit_code 退出的状态码，范围是 0～255
     * @param int $signal 进程退出的信号
     * @return void
    */
    public function run($server, $worker_id, $worker_pid, $exit_code, $signal) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \app\event\websocket\onWorkerError;
        $on->run($server, $worker_id, $worker_pid, $exit_code, $signal);
    }
}

