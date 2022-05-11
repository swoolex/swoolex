<?php
/**
 * +----------------------------------------------------------------------
 * 异步任务完成时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\rpc;

class onFinish {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @param Swoole $server
     * @param int $task_id 执行任务的 task 进程 id
     * @param mixed $data 任务处理的结果内容
    */
    public function run($server, $task_id, $data) {
        $this->server = $server;
        
        // 业务挂载
        $this->rpc($server, $task_id, $data);

        // 调用二次转发，不做重载
        $on = new \box\event\server\onFinish;
        $on->run($server, $task_id, $data);
    }

    /**
     * 微服务TCP服务
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
    */
    private function rpc($server, $task_id, $data) {
        $param = json_decode($data, true);
        // 节点信息
        $config = $param['config'];
        // 返回值信息
        $data = $param['data'];

        // 需要异步回调通知
        if (!empty($config['callback'])) {
            $body = [
                'code' => 200,
                'msg' => 'rpc finish success',
                'data' => $data,
            ];
            $httpClient = (new \x\Client())->http();
            if ($config['callback_type'] == 'post') {
                $httpClient->domain($config['callback'])
                ->body($body)
                ->post();
            } else {
                $httpClient->domain($config['callback'])
                ->body($body)
                ->get();
            }
        }
    }
}

