<?php
// +----------------------------------------------------------------------
// | 监听进程启动事件
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onStart
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
     * @return void
    */
    public function run($server) {
        try {
            $this->server = $server;

            $config = \x\Config::get('server');
            //如果是以Daemon形式开启的服务，记录master和manager的进程id
            if ($config['daemonize'] === true) {
                file_put_contents($config['pid_file'], json_encode([
                    'master_pid' => $server->master_pid,
                    'manager_pid' => $server->manager_pid,
                ]));
                // 创建tasker进程文件 和 worker进程文件
                // tasker和worker进程的pid将会在workerstart回调中写入到文件中
                touch($config['worker_pid_file']);
                touch($config['tasker_pid_file']);
            }
            
            // 调用二次转发，不做重载
            $on = new \app\event\onStart;
            $on->run($server);
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }

}

