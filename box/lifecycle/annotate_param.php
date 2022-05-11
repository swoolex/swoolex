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

namespace box\lifecycle;

class annotate_param
{
    /**
     * 接受回调处理
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return bool
    */
    public function run($server, $fd, $tips, $name, $status, $attach) {
        if (!$tips) {
            $tips = 'Annotate：Param filter Error，Name：'.$name.' ，SW-X Status：'.$status;
            if ($attach) {
                $tips .= '， Attach：'.$attach;
            }
        }
        
        $type = \x\Config::get('server.sw_service_type');
        // HTTP请求
        if ($type == 'http') {
            $obj = new \x\controller\Http();
            $obj->fetch($tips);
        // websocket请求
        } else if($type == 'websocket') {
            if (\x\context\Container::get('websocket_frame')) {
                $obj = new \x\controller\WebSocket();
                $obj->fetch('annotate_param_error', 'error', $tips);
            } else {
                $obj = new \x\controller\Http();
                $obj->fetch($tips);
            }
        // Rpc请求
        } else if($type == 'rpc') {
            $ServerCurrency = new \x\rpc\ServerCurrency();
            $ServerCurrency->returnJson($server,  $fd, '200', 'annotate_param_error', $tips);
        // MQTT请求
        } else if($type == 'mqtt') {
            $data = [
                'type' => \x\mqtt\common\Types::DISCONNECT,
                'msg' => $tips,
            ];
            $level = (new \x\mqtt\Table($server))->deviceLevel($fd);
            if ($level === 5) {
                $class = '\x\mqtt\v5\Dc';
            } else {
                $class = '\x\mqtt\v3\Dc';
            }
            $server->send($fd, $class::pack($data));
        }
        return true;
    }
}