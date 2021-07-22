<?php
/**
 * +----------------------------------------------------------------------
 * 接收到unixSocket时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\event\server;

class onPipeMessage
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
     * @param int $src_worker_id 消息来自哪个 Worker 进程
     * @param mixed $message 消息内容，可以是任意 PHP 类型
     * @return void
    */
    public function run($server, $src_worker_id, $message) {
        $this->server = $server;
        
    }
}

