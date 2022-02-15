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

namespace event\tcp;

class onFinish {
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
     * @param int $task_id 执行任务的 task 进程 id
     * @param mixed $data 任务处理的结果内容
     * @return void
    */
    public function run($server, $task_id, $data) {
        $this->server = $server;
        
        // 调用二次转发，不做重载
        $on = new \box\event\server\onFinish;
        $on->run($server, $task_id, $data);
    }
}

