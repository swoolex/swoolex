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
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $service 实例
     * @return void
    */
    protected function event_binding($service, $config) {
        \x\StartEo::run(\x\Lang::run()->get('start -13'));

        # 监听进程启动事件
        $service->on('Start', [new \event\onStart, 'run']);
        # 监听进程关闭事件
        $service->on('Shutdown', [new \event\onShutdown, 'run']);
        # Worker 进程 / Task 进程启动
        $service->on('WorkerStart', [new \event\onWorkerStart, 'run']);
        # 在 (Worker) 进程终止时发生
        $service->on('WorkerStop', [new \event\onWorkerStop, 'run']);
        # 在 (Worker) 进程重启前触发
        $service->on('WorkerExit', [new \event\onWorkerExit, 'run']);
        # 有新的连接进入
        $service->on('Connect', [new \event\onConnect, 'run']);
        # 接收到数据时
        $service->on('Receive', [new \event\onReceive, 'run']);
        # 接收到 UDP 数据包时
        $service->on('Packet', [new \event\onPacket, 'run']);
        # 监听客户端退出事件
        $service->on('Close', [new \event\onClose, 'run']);
        # 接收到异步任务时
        $service->on('Task', [new \event\onTask, 'run']);
        # 异步任务完成时
        $service->on('Finish', [new \event\onFinish, 'run']);
        # 接收到unixSocket时
        $service->on('PipeMessage', [new \event\onPipeMessage, 'run']);
        # Worker/Task 进程发生异常后
        $service->on('WorkerError', [new \event\onWorkerError, 'run']);
        # 当管理进程启动时
        $service->on('ManagerStart', [new \event\onManagerStart, 'run']);
        # 当管理进程结束时
        $service->on('ManagerStop', [new \event\onManagerStop, 'run']);
        # Worker进程重载前
        $service->on('BeforeReload', [new \event\onBeforeReload, 'run']);
        # Worker进程重载后
        $service->on('AfterReload', [new \event\onAfterReload, 'run']);

        # 监听WebSokcet握手过程
        if (isset($config['is_onHandShake']) && $config['is_onHandShake']==true) {
            $service->on('HandShake', [new \event\onHandShake($service), 'run']);
        }

        # 监听WebSocket握手成功
        $service->on('Open', [new \event\onOpen, 'run']);

        # 监听客户端消息发送请求
        if (isset($config['is_onMessage']) && $config['is_onMessage']==true) {
            # 开启系统分包流程
            $service->on('Message', [new \event\onMessage, 'run']);
        } else {
            $service->on('Message', [new \app\event\onMessage, 'run']);
        }

        # 监听外部调用请求
        $service->on('Request', [new \event\onRequest($service, $config), 'run']);

        \x\StartEo::run(\x\Lang::run()->get('start -14'));

        # 启动服务
        $service->start();
    }
}