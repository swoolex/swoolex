<?php
// +----------------------------------------------------------------------
// | 当WebSocket->Push消息失败时，系统回调的生命周期
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace lifecycle;

class websocket_push_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.2.5 + 2020.07.21
     * @deprecated 暂不启用
     * @global 无
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