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
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $server
     * @param int $workerId 进程ID
     * @return void
    */
    public function run($server, $workerId) {
        $this->server = $server;

        // 启动数据库连接池
        $this->start_mysql();
        // 启动Redis连接池
        $this->start_redis();
        
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
    private function start_mysql() {
        // 启动数据库连接池
        \x\db\MysqlPool::run()->init();
        // 启动连接池检测定时器
        \x\db\MysqlPool::run()->timing_recovery(\x\Config::run()->get('mysql.mysql_timing_recovery'));
        \x\StartEo::run(\x\Lang::run()->get('mysql yes').'，当前worker_id：'.$this->server->worker_id);
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
    private function start_redis() {
        if (\x\Config::run()->get('redis.status')) {
            // 启动数据库连接池
            \x\redis\RedisPool::run()->init();
            // 启动连接池检测定时器
            \x\redis\RedisPool::run()->timing_recovery(\x\Config::run()->get('redis.redis_timing_recovery'));
            \x\StartEo::run(\x\Lang::run()->get('redis yes').'，当前worker_id：'.$this->server->worker_id);
        }
    }
}

