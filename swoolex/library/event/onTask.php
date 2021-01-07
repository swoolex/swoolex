<?php
// +----------------------------------------------------------------------
// | 接收到异步任务时
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onTask
{
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
     * @param Swoole $server
     * @param int $task_id 执行任务的 task 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param int $src_worker_id 投递任务的 worker 进程 id【$task_id 和 $src_worker_id 组合起来才是全局唯一的，不同的 worker 进程投递的任务 ID 可能会有相同】
     * @param mixed $data 是任务的数据内容
     * @return void
    */
    public function run($server, $task_id, $src_worker_id, $data) {
        try {
            $this->server = $server;
            
            // 调用二次转发，不做重载
            $on = new \app\event\onTask;
            $on->run($server, $task_id, $src_worker_id, $data);
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }
}

