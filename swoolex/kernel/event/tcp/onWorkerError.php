<?php
/**
 * +----------------------------------------------------------------------
 * Worker/Task 进程发生异常后
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\tcp;

class onWorkerError {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param Swoole $server
     * @param int $worker_id 异常 worker 进程的 id
     * @param int $worker_pid 异常 worker 进程的 pid
     * @param int $exit_code 退出的状态码，范围是 0～255
     * @param int $signal 进程退出的信号
    */
    public function run($server, $worker_id, $worker_pid, $exit_code, $signal) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \box\event\server\onWorkerError;
        $on->run($server, $worker_id, $worker_pid, $exit_code, $signal);
    }
}

