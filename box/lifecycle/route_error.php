<?php
/**
 * +----------------------------------------------------------------------
 * 当除了Param注解外，其他注解校验失败时，系统回调处理的生命周期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class route_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param string $status 错误事件状态码
     * @return bool
    */
    public function run($server, $fd, $status) {
        $tips = 'Annotate：SW-X Status：'.$status.' ERROR ！';

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
            $ServerCurrency->returnJson($server,  $fd, '200', 'route_error', $tips);
        // MQTT请求
        } else if($type == 'mqtt') {
            $data = [
                'type' => \x\mqtt\common\Types::DISCONNECT,
                'msg' => $tips,
            ];
            if (\x\Config::get('mqtt.protocol_level') == 5) {
                $server->send($fd, \x\mqtt\v5\Dc::pack($data));
            } else {
                $server->send($fd, \x\mqtt\v3\Dc::pack($data));
            }
        }
        unset($obj);
        return true;
    }
}