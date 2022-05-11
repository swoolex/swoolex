<?php
/**
 * +----------------------------------------------------------------------
 * Worker 进程 / Task 进程启动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\tcp;

class onWorkerStart {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @param Swoole $server
     * @param int $workerId 进程ID
    */
    public function run($server, $workerId) {
        $this->server = $server;

        $this->mount($workerId);

        // 调用二次转发，不做重载
        $on = new \box\event\server\onWorkerStart;
        $on->run($server, $workerId);
        
        // 生命周期转发
        \design\Lifecycle::worker_start($workerId);
    }

    /**
     * 任务挂载
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @param int $workerId 进程ID
    */
    private function mount($workerId) {
        // 挂载PID-ENV更新
        \design\MountEvent::WorkerStart_PidENV($this->server, $workerId);
        // 载入雪花分布式ID组件
        \design\MountEvent::WorkerStart_Snowflake($workerId);
        // 载入定时任务
        \design\MountEvent::WorkerStart_Crontab($this->server, $workerId);
    }
}