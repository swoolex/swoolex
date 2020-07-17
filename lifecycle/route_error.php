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
     * @param string $request 请求对象
     * @param string $response 请求实例
     * @param string $service_type SW的服务类型 http||websocket
     * @param string $status 错误事件状态码
     * @return bool
    */
    public function run($request, $response, $service_type, $status) {
        $tips = 'Annotate：SW-X Status：'.$status.' ERROR ！';
        // HTTP请求
        if ($service_type == 'http') {
            $obj = new \x\Controller();
            $obj->setRequest($request);
            $obj->setResponse($response);
            $obj->fetch($tips);
        // websocket请求
        } else {
            $obj = new \x\WebSocket();
            $obj->setServer($request);
            $obj->setFrame($response);
            $obj->fetch('route_error', 'error', $tips);
        }
        unset($obj);
        return true;
    }
}