<?php
// +----------------------------------------------------------------------
// | 服务启动基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\service;

abstract class Service
{
    /**
     * 服务类型
    */
    private $type;
    /**
     * 配置
    */
    private $config;
  
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
    abstract public function start($set);

    /**
     * 合并配置项
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $left 被合并的配置
     * @param array $right 需要合并的配置
     * @return array
    */
    protected function merge_set($left, $right) {
        foreach ($right as $key=>$value) {
            $left[$key] = $value;
        }
        return $left;
    }

    /**
     * 事件绑定
     * @todo 无
     * @author 小黄牛
     * @version v1.0.2 + 2020.06.12
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $service 实例
     * @param array $config 配置
     * @param string $type 服务类型
     * @return void
    */
    protected function event_binding($service, $config, $type) {
        \x\StartEo::run(\x\Lang::run()->get('start -13'));
        $this->type = $type;
        $this->config = $config;

        # 监听进程启动事件
        $service->on('Start', [$this->ioc('onStart'), 'run']);
        # 监听进程关闭事件
        $service->on('Shutdown', [$this->ioc('onShutdown'), 'run']);
        # Worker 进程 / Task 进程启动
        $service->on('WorkerStart', [$this->ioc('onWorkerStart'), 'run']);
        # 在 (Worker) 进程终止时发生
        $service->on('WorkerStop', [$this->ioc('onWorkerStop'), 'run']);
        # 在 (Worker) 进程重启前触发
        $service->on('WorkerExit', [$this->ioc('onWorkerExit'), 'run']);
        # 有新的连接进入
        $service->on('Connect', [$this->ioc('onConnect'), 'run']);
        # 接收到数据时
        $service->on('Receive', [$this->ioc('onReceive'), 'run']);
        # 接收到 UDP 数据包时
        $service->on('Packet', [$this->ioc('onPacket'), 'run']);
        # 监听客户端退出事件
        $service->on('Close', [$this->ioc('onClose'), 'run']);
        # 接收到异步任务时
        $service->on('Task', [$this->ioc('onTask'), 'run']);
        # 异步任务完成时
        $service->on('Finish', [$this->ioc('onFinish'), 'run']);
        # 接收到unixSocket时
        $service->on('PipeMessage', [$this->ioc('onPipeMessage'), 'run']);
        # Worker/Task 进程发生异常后
        $service->on('WorkerError', [$this->ioc('onWorkerError'), 'run']);
        # 当管理进程启动时
        $service->on('ManagerStart', [$this->ioc('onManagerStart'), 'run']);
        # 当管理进程结束时
        $service->on('ManagerStop', [$this->ioc('onManagerStop'), 'run']);
        # Worker进程重载前
        $service->on('BeforeReload', [$this->ioc('onBeforeReload'), 'run']);
        # Worker进程重载后
        $service->on('AfterReload', [$this->ioc('onAfterReload'), 'run']);

        # 监听WebSokcet握手过程
        if (isset($config['is_onHandShake']) && $config['is_onHandShake']==true && $type == 'websocket') {
            $service->on('HandShake', [$this->ioc('onHandShake', $service), 'run']);
        }

        # 监听WebSocket握手成功
        if ($type == 'websocket') {
            $service->on('Open', [$this->ioc('onOpen'), 'run']);
        }

        # 监听客户端消息发送请求
        if ($type == 'websocket') {
            $service->on('Message', [$this->ioc('onMessage'), 'run']);
        }

        # 监听外部调用请求
        $service->on('Request', [$this->ioc('onRequest', $service, $config), 'run']);

        \x\StartEo::run(\x\Lang::run()->get('start -14'));

        # 启动服务
        $service->start();
    }

    /**
     * 构造回调事件的new对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.2 + 2020.06.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $event 事件对象名称
     * @param array $argc 其余参数
     * @return void
    */
    private function ioc($event, ...$argc) {
        $class = '\event\\'.$this->type.'\\'.$event;
        if ($event == 'onMessage') {
            if (!isset($this->config['is_onMessage']) || $this->config['is_onMessage'] != true) {
                # 关闭系统分包流程
                $class = '\app'.$class;
            }
        }
        
        $reflection = new \ReflectionClass($class);
        return $reflection->newInstanceArgs($argc);
    }
}