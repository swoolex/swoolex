<?php
// +----------------------------------------------------------------------
// | WebSocket端
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\service\WebSocket;
use x\service\Service;

class WebSocket extends Service
{
    /**
	 * 启动实例
	*/
    private $service;
    /**
     * 类型 
    */
    private $type = 'WebSocket';
    
    /**
     * 应用启动入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $set 配置项
     * @return void
    */
    public function start($set=[]) {
        \x\StartEo::run($this->type . \x\Lang::run()->get('start -8'));

        $config = $this->merge_set(\x\Config::run()->get('websocket'), $set);

        # WSS配置
        $wss = SWOOLE_SOCK_TCP;

        $set = [
            'open_http2_protocol' => $config['open_http2_protocol'],
            'task_worker_num' => $config['task_worker_num'],
            'task_ipc_mode' => $config['task_ipc_mode'],
            'task_max_request' => $config['task_max_request'],
            'task_enable_coroutine' => $config['task_enable_coroutine'],
            'task_use_object' => $config['task_use_object'],
            'dispatch_mode' => $config['dispatch_mode'],
            'daemonize' => $config['daemonize'],
            'log_level' => $config['log_level'],
            'open_tcp_keepalive' => $config['open_tcp_keepalive'],
            'heartbeat_check_interval' => $config['heartbeat_check_interval'],
            'heartbeat_idle_time' => $config['heartbeat_idle_time'],
        ];

        if ($config['backlog']) $set['backlog'] = $config['backlog'];
        if ($config['reactor_num']) $set['reactor_num'] = $config['reactor_num'];
        if ($config['worker_num']) $set['worker_num'] = $config['worker_num'];
        if ($config['max_request']) $set['max_request'] = $config['max_request'];
        if ($config['max_conn']) $set['max_conn'] = $config['max_conn'];
        if ($config['task_tmpdir']) $set['task_tmpdir'] = $config['task_tmpdir'];
        if ($config['log_file']) $set['log_file'] = $config['log_file'];

        // 配置HTTPS
        if ($config['ssl_cert_file'] && $config['ssl_key_file']) {
            $set['ssl_cert_file'] = $config['ssl_cert_file'];
            $set['ssl_key_file'] = $config['ssl_key_file'];
            $wss = SWOOLE_SOCK_TCP | SWOOLE_SSL;
            \x\StartEo::run($this->type . \x\Lang::run()->get('start -9'));
        }

        $this->service = new \Swoole\WebSocket\Server($config['host'], $config['port'], SWOOLE_PROCESS, $wss);
        if ($this->service) {
            \x\StartEo::run($this->type . \x\Lang::run()->get('start -10'));
        } else {
            \x\StartEo::run($this->type . \x\Lang::run()->get('start -11'), 'error');
        }

        // 注入配置
        $this->service->set($set);
        \x\StartEo::run($this->type . \x\Lang::run()->get('start -12'), 'websocket');
        // 返回父类，进行事件绑定
        $this->event_binding($this->service, $config, 'websocket');
    }
}