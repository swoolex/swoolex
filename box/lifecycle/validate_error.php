<?php
/**
 * +----------------------------------------------------------------------
 * Validate验证器注解检测失败时，回调的处理函数
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class validate_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型 http/websocket/rpc/mqtt
     * @param bool $batch 是否全部过滤
     * @param array $errors 错误验证结果集
     * @return bool
    */
    public function run($server_type, $batch, $errors) {
        // $batch 是用于执行全部过滤规则,再最后一起返回全部的错误原因
        // 默认生命周期只返回第一个错误原因
        $error = $errors[0]['intact_field'].' => '.$errors[0]['message'];

        // HTTP请求
        if ($server_type == 'http') {
            $obj = new \x\controller\Http();
            $obj->fetch($error);
        // websocket请求
        } else if($type == 'websocket') {
            $obj = new \x\controller\WebSocket();
            $obj->fetch('controller_error', 'error', $error);
        // Rpc请求
        } else if($server_type == 'rpc') {
            $ServerCurrency = new \x\rpc\ServerCurrency();
            $ServerCurrency->returnJson(
                \x\context\Container::get('server'),  
                \x\context\Container::get('fd'), 
                '200', 
                'controller_error', 
                $error
            );
        // MQTT请求
        } else if($server_type == 'mqtt') {
            $server = \x\context\Container::get('server');
            $fd = \x\context\Container::get('fd');
            $data = [
                'type' => \x\mqtt\common\Types::DISCONNECT,
                'msg' => $error,
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