<?php
// +----------------------------------------------------------------------
// | Worker 进程 / Task 进程启动
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onWorkerStart
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $workerId 进程ID
     * @return void
    */
    public function run($server, $workerId) {
        $this->server = $server;

        // 初始化路由表
        \x\doc\Table::run()->start();

        $config = \x\Config::run()->get('server');
        /*
        可以将公用的，不易变的php文件放置到onWorkerStart之前。
        这样虽然不能重载入代码，
        但所有worker是共享的，不需要额外的内存来保存这些数据。
        onWorkerStart之后的代码每个worker都需要在内存中保存一份
        workerId大于配置文件中worker_num的，
        则为task worker进程，反则是普通worker进程
        */
        if ($workerId >= $config['worker_num']){
            swoole_set_process_name($config['tasker']);

            if (is_file($config['tasker_pid_file'])) {
                file_put_contents($config['tasker_pid_file'], $workerId.':'.$server->worker_pid.'|', FILE_APPEND);
            }
        } else {
            swoole_set_process_name($config['worker']);
            if (is_file($config['worker_pid_file'])) {
                file_put_contents($config['worker_pid_file'], $workerId.':'.$server->worker_pid.'|', FILE_APPEND );
            }
        }

        // 启动数据库连接池
        $this->start_mysql($workerId);
        // 启动Redis连接池
        $this->start_redis($workerId);
        
        // 调用二次转发，不做重载
        $on = new \app\event\onWorkerStart;
        $on->run($server, $workerId);
    }
    
    /**
     * 打开Mysql连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function start_mysql($workerId) {
        // 启动数据库连接池
        \x\db\MysqlPool::run()->init();
        // 启动连接池检测定时器
        \x\db\MysqlPool::run()->timing_recovery(\x\Config::run()->get('mysql.mysql_timing_recovery'), $workerId);

    }

    
    /**
     * 打开Redis连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function start_redis($workerId) {
        if (\x\Config::run()->get('redis.status')) {
            // 启动数据库连接池
            \x\redis\RedisPool::run()->init();
            // 启动连接池检测定时器
            \x\redis\RedisPool::run()->timing_recovery(\x\Config::run()->get('redis.redis_timing_recovery'), $workerId);
        }
    }
}

