<?php
// +----------------------------------------------------------------------
// | 异步任务完成时
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onFinish
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
     * @param int $task_id 执行任务的 task 进程 id
     * @param mixed $data 任务处理的结果内容
     * @return void
    */
    public function run($server, $task_id, $data) {
        try {
            $this->server = $server;
            // 微服务
            if (\x\Config::run()->get('server.sw_service_type') == 'rpc') {
                $this->rpc($server, $task_id, $data);
            } else {
                $this->server($server, $task_id, $data);
            }
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }

    
    /**
     * 微服务TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function rpc($server, $task_id, $data) {
        
    }

    /**
     * 普通TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function server($server, $task_id, $data) {
        // 调用二次转发，不做重载
        $on = new \app\event\onFinish;
        $on->run($server, $task_id, $data);
    }
}

