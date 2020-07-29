<?php
// +----------------------------------------------------------------------
// | 当除了Param注解外，其他注解校验失败时，系统回调处理的生命周期
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace lifecycle;

class route_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $status 错误事件状态码
     * @return bool
    */
    public function run($status) {
        $tips = 'Annotate：SW-X Status：'.$status.' ERROR ！';
        // HTTP请求
        if (\x\Container::getInstance()->has('request')) {
            $obj = new \x\Controller();
            $obj->fetch($tips);
        // websocket请求
        } else if(\x\Container::getInstance()->has('server')) {
            $obj = new \x\WebSocket();
            $obj->fetch('route_error', 'error', $tips);
        }
        unset($obj);
        return true;
    }
}