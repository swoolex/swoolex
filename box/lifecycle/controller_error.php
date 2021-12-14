<?php
/**
 * +----------------------------------------------------------------------
 * 当应用层捕捉到错误时，系统回调处理的生命周期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class controller_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param array $e 错误内容
     * @param string $error 系统自定义错误描述
     * @param array $source 错误上下文内容
     * @return bool
    */
    public function run($e, $error, $source) {
        $type = \x\Config::get('server.sw_service_type');

        // HTTP请求
        if ($type == 'http') {
            $obj = $this->http($e, $error, $source);
        // websocket请求
        } else if($type == 'websocket') {
            if (\x\context\Container::get('websocket_frame')) {
                $obj = new \x\controller\WebSocket();
                $obj->fetch('controller_error', 'error', $error);
            } else {
                $obj = $this->http($e, $error, $source);
            }
        // Rpc请求
        } else if($type == 'rpc') {
            $ServerCurrency = new \x\rpc\ServerCurrency();
            $ServerCurrency->returnJson(
                \x\context\Container::get('server'),  
                \x\context\Container::get('fd'), 
                '200', 
                'controller_error', 
                $error
            );
        // MQTT请求
        } else if($type == 'mqtt') {
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

    /**
     * HTTP服务的错误界面
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function http($e, $error, $source) {
        // 开启调试模式则记录错误日志
        if (\x\Config::get('app.de_bug') == true) {
            # 引入详细报错页面
            $exceptionFile = EXAMPLES_PATH.'tpl/error_test.php';
        } else {
            # 引入简单报错页面
            $exceptionFile = EXAMPLES_PATH.'tpl/error_formal.php';
        }

        // 引入文件
        ob_start();
        include $exceptionFile;
        $html = ob_get_clean();

        $obj = new \x\controller\Http();
        $obj->fetch($html);
        unset($obj);
    }
}