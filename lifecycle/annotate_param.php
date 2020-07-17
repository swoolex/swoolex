<?php
// +----------------------------------------------------------------------
// | 当注解Param检测失败时，系统默认回调处理的生命周期
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace lifecycle;

class annotate_param
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $request 请求对象
     * @param string $response 请求实例
     * @param string $service_type SW的服务类型 http||websocket
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return bool
    */
    public function run($request, $response, $service_type, $tips, $name, $status, $attach) {
        if (!$tips) {
            $tips = 'Annotate：Param filter Error，Name：'.$name.' ，SW-X Status：'.$status;
            if ($attach) {
                $tips .= '， Attach：'.$attach;
            }
        }
        // HTTP请求
        if ($service_type == 'http') {
            $obj = new \app\controller\SuperClass\DriverApi();
            $obj->setRequest($request);
            $obj->setResponse($response);
            $obj->returnJson('-1', $tips);
        // websocket请求
        } else {
            $obj = new \x\WebSocket();
            $obj->setServer($request);
            $obj->setFrame($response);
            $obj->fetch('annotate_param_error', 'error', $tips);
        }
        return true;
    }
}