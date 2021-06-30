<?php
/**
 * +----------------------------------------------------------------------
 * 当注解Param检测失败时，系统默认回调处理的生命周期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace other\lifecycle;

class annotate_param
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return bool
    */
    public function run($tips, $name, $status, $attach) {
        if (!$tips) {
            $tips = 'Annotate：Param filter Error，Name：'.$name.' ，SW-X Status：'.$status;
            if ($attach) {
                $tips .= '， Attach：'.$attach;
            }
        }
        
        $type = \x\Config::get('server.sw_service_type');
        // HTTP请求
        if ($type == 'http') {
            $obj = new \x\controller();
            $obj->fetch($tips);
        // websocket请求
        } else if($type == 'websocket') {
            $obj = new \x\WebSocket();
            $obj->fetch('annotate_param_error', 'error', $tips);
        }
        return true;
    }
}