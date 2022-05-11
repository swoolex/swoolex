<?php
/**
 * +----------------------------------------------------------------------
 * 当WebSocket->Push消息失败时，系统回调的生命周期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class websocket_push_error
{
    /**
     * 接受回调处理
     * @author 小黄牛
     * @version v1.2.5 + 2020.07.21
     * @param Server $server 实例
     * @param json $content push的内容
     * @param int $fd 客户端标识
     * @return bool
    */
    public function run($server, $content, $fd) {
        // 可以自己处理重新push逻辑
        // $server->push($fd, $content);

        return true;
    }
}