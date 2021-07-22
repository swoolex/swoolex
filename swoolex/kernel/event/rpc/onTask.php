<?php
/**
 * +----------------------------------------------------------------------
 * 接收到异步任务时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\rpc;

class onTask {
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

            // 业务挂载
            $this->rpc($server, $task);

            // 调用二次转发，不做重载
            $on = new \box\event\server\onTask;
            $on->run($server, $task);
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
        # 开始转发路由
        $config = json_decode($task->data, true);
        $obj = new \x\rpc\ServerRoute();
        $ret = $obj->start($server, 0, 0, $config);

        # 配置传输
        $array = [
            'config' => $config,
            'data' => $ret,
        ];

        // 异步通知
        $task->finish(json_encode($array, JSON_UNESCAPED_UNICODE));

        // 销毁整个请求级容器
        \x\context\Container::clear();
    }
}

