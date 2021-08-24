<?php
/**
 * +----------------------------------------------------------------------
 * Swoole消息事件中，需要统一挂载的公共组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;
use design\SystemTips;

class MountEvent {
    //---------------------------- onStart 阶段 ------------------------------------
    /**
     * 更新服务PID-ENV缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @return void
    */
    public static function Start_PidENV($server) {
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
    }

    //---------------------------- onManagerStart 阶段 -----------------------------
    /**
     * 更新服务进程别名
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function ManagerStart_NameReload() {
        $config = \x\Config::get('server');
        swoole_set_process_name($config['manager']);
    }

    //---------------------------- onWorkerStart 阶段 -----------------------------
    /**
     * 更新服务PID-ENV缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $workerId 进程ID
     * @return void
    */
    public static function WorkerStart_PidENV($server, $workerId) {
        $config = \x\Config::get('server');
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
                \Swoole\Coroutine\System::writeFile($config['tasker_pid_file'], $workerId.':'.$server->worker_pid.'|', FILE_APPEND);
            }
        } else {
            swoole_set_process_name($config['worker']);
            if (is_file($config['worker_pid_file'])) {
                \Swoole\Coroutine\System::writeFile($config['worker_pid_file'], $workerId.':'.$server->worker_pid.'|', FILE_APPEND );
            }
        }
    }
    /**
     * 定时任务挂载
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $workerId 进程ID
     * @return void
    */
    public static function WorkerStart_Crontab($server, $workerId) {
        // 只有第一个worker进程才能挂载任务，否则会造成重发任务并行
        if ($workerId != 0) return false;

        // 读取定时任务列表
        $crontab_list = \x\Config::get('crontab');
        foreach ($crontab_list as $v) {
            if (isset($v['status']) && $v['status']==false) continue;
            if (!isset($v['use']) || !isset($v['rule'])) continue;

            if (!class_exists($v['use'])) {
                // 此处需要关闭服务，否则worker将一直重启
                $server->shutdown();
                throw new \x\exception\CrontabException(SystemTips::CRONTAB_1.$v['use']);
                return false;
            }

            // 载入定时器
            $obj = new $v['use'];
            // 分解规则参数
            $rule = $obj->rule_cutting($v['rule']);
            if (!$rule) {
                unset($obj);
                continue;
            }
            // 写入Swoole实例
            $obj->setServer($server);
            // 写入规则
            $v['rule'] = $rule;
            $obj->setRule($v);
            // 日志文件地址
            $path = WORKLOG_PATH.'crontab'.DS.str_replace('\\', '_', $v['use']).'.log';
            // Linux风格
            if (is_array($rule)) {
                // 1秒一次
                \Swoole\Timer::tick(1000, function ($timer_id) use ($server, $obj, $v) {
                    // 查看任务是否达到执行时间
                    $status = $obj->task_vif($v['rule']);
                    if ($status) {
                        // 写入任务ID
                        $obj->setTimerId($timer_id);
                        // 开始任务
                        $res = $obj->run();
                        // 记录日志
                        if (isset($v['bin_log']) && $v['bin_log']==true) {
                            $log = date('Y-m-d H:i:s', time()).'，返回值：'.($res ?: '无');
                            \Swoole\Coroutine\System::writeFile($path, $log);
                        }
                    }
                });
            // 自定义毫秒
            } else {
                \Swoole\Timer::tick($rule, function ($timer_id) use ($server, $obj, $v) {
                    // 写入任务ID
                    $obj->setTimerId($timer_id);
                    // 开始任务
                    $res = $obj->run();
                    // 记录日志
                    if (isset($v['bin_log']) && $v['bin_log']==true) {
                        $log = date('Y-m-d H:i:s', time()).'，返回值：'.($res ?: '无');
                        \Swoole\Coroutine\System::writeFile($path, $log);
                    }
                });
            }
        }

        \design\StartRecord::crontab();
    }

    /**
     * MQTT设备在线状态更新
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $workerId 进程ID
     * @return void
    */
    public static function WorkerStart_MqttStatus($server, $workerId) {
        // 只有第一个worker进程才能挂载任务，否则会造成重发任务并行
        if ($workerId != 0) return false;

        $time = \x\Config::get('mqtt.ping_crontab_time')*1000;
        $ping_max_time = \x\Config::get('mqtt.ping_max_time');

        \Swoole\Timer::tick($time, function ($timer_id) use ($server, $ping_max_time) {
            $times = time();
            foreach ($this->server->device_list as $v) {
                // 过期了
                if ($v['status'] == 1 && ($v['ping_time']+$ping_max_time) < $times) {
                    $this->server->device_list->set($v['client_id'], [
                        'status' => 2, // 离线
                    ]);
                }
            }
        });

        \design\StartRecord::mqtt_service_monitor();
    }

    /**
     * HTTP-初始化路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function WorkerStart_RouteStart_Http() {
        \x\route\doc\Table::run()->start_http();

        \design\StartRecord::http_doc_reload();
    }

    /**
     * WebSocket-初始化路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function WorkerStart_RouteStart_WebSocket() {
        \x\route\doc\Table::run()->start_websocket();

        \design\StartRecord::websocket_doc_reload();
    }

    /**
     * Rpc-初始化路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.2 + 2021.08.24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function WorkerStart_RouteStart_Rpc() {
        \x\route\doc\Table::run()->start_rpc();

        \design\StartRecord::rpc_doc_reload();
    }

    /**
     * Mqtt-初始化路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.2 + 2021.08.24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function WorkerStart_RouteStart_Mqtt() {
        \x\route\doc\Table::run()->start_mqtt();

        \design\StartRecord::mqtt_doc_reload();
    }

    /**
     * 初始化微服务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param int $workerId 进程ID
     * @return void
    */
    public static function WorkerStart_RpcClient($workerId) {
        // 只有第一个worker进程才能挂载任务，否则会造成重发任务并行
        if ($workerId != 0) return false;

        // 初始化微服务
        if (\x\Config::get('rpc.http_rpc_is') != true) return false;
        
        \x\Rpc::run()->start();
    }

    /**
     * 打开Mysql连接池
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param int $workerId 进程ID
     * @return void
    */
    public static  function WorkerStart_MysqlStart($workerId) {
        if (\x\Config::get('mysql.driver') == 'mysql') {
            // 启动数据库连接池
            \x\db\mysql\Pool::run()->init();
            // 启动连接池检测定时器
            \x\db\mysql\Pool::run()->timing_recovery($workerId);
        }
    }
    
    /**
     * 打开Redis连接池
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param int $workerId 进程ID
     * @return void
    */
    public static  function WorkerStart_RedisStart($workerId) {
        // 启动数据库连接池
        \x\redis\Pool::run()->init();
        // 启动连接池检测定时器
        \x\redis\Pool::run()->timing_recovery($workerId);
    }
}