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
     * @param Task $task
     * @param mixed $data 是任务的数据内容
     * @return void
    */
    public function run($server, $task) {
        try {
            $this->server = $server;
            // 微服务
            if (\x\Config::run()->get('server.sw_service_type') == 'rpc') {
                $this->rpc($server, $task);
            } else {
                $this->server($server, $task);
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
    private function rpc($server, $task) {
        $data = json_decode($data->data, true);

        # 开始转发路由
        $obj = new \x\rpc\ServerRoute();
        $ret = $obj->start($server, 0, 0, json_decode($task->data, true));

        if (is_array($ret)) $ret = json_encode($ret, JSON_UNESCAPED_UNICODE);
        // 异步通知
        $task->finish($ret);

        // 销毁整个请求级容器
        \x\Container::getInstance()->clear();
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
    private function server($server, $task) {
        // 调用二次转发，不做重载
        $on = new \app\event\onTask;
        $on->run($server, $task);
    }
}

