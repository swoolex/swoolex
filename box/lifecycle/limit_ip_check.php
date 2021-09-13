<?php
/**
 * +----------------------------------------------------------------------
 * IP限流器达到峰值时，回调的通知函数
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class limit_ip_check
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param string $server_type 服务类型 http/websocket/rpc/mqtt
     * @param string $ip 触发IP
     * @param string $data 对应限流配置信息
     * @return void
    */
    public function run($server, $fd, $server_type, $ip, $data) {
        $msg = $ip.' 已被限制，'.$data['time'].'s 内，只允许访问'.$data['peak'].'次！';
        switch ($server_type) {
            case 'http':
                $obj = new \x\controller\Http();
                $obj->fetch($msg);
            break;
            case 'websocket':
                $obj = new \x\controller\WebSocket();
                $obj->fetch('limit_error', 'error', $msg);
            break;
            case 'rpc':
                $ServerCurrency = new \x\rpc\ServerCurrency();
                $ServerCurrency->returnJson($server,  $fd, '200', 'RPC Ip LIMIT', $msg);
            break;
            case 'mqtt':
                $data = [
                    'type' => \x\mqtt\common\Types::DISCONNECT,
                    'msg' => $msg,
                ];
                $arr = $server->fds[$fd];
                $class = $arr['class'];
                $server->send($fd, $class::pack($data));
                $server->close($fd);
            break;
        }
        return true;
    }
}