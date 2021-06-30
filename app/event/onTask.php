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

namespace app\event;

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
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param Task $task
     * @param mixed $data 是任务的数据内容
     * @return void
    */
    public function run($server, $task) {
        $this->server = $server;
        
    }
}

