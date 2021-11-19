<?php
/**
 * +----------------------------------------------------------------------
 * 中间件基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Middleware {
    /**
     * 植入参数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-19
     * @deprecated 暂不启用
     * @global 无
     * @param string $server 服务实例
     * @param mixed $fd 客户端标识符
     * @param string $service_type 服务类型
     * @return void
    */
    public final function __construct($server, $fd, $service_type) {
        $this->server = $server;
        $this->fd = $fd;
        $this->service_type = $service_type;
    }

    /**
     * 抛出内容给请求
     * @todo 无
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-19
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $mixed
     * @return void
    */
    public final function error($mixed) {
        switch ($this->service_type) {
            case 'http':
                if (is_array($mixed)) $mixed = json_encode($mixed, JSON_UNESCAPED_UNICODE);
                $obj = new \x\controller\Http();
                $obj->fetch($mixed);
            break;
            case 'websocket':
                $obj = new \x\controller\WebSocket();
                $obj->fetch('middleware_error', 'error', $mixed);
            break;
            case 'rpc':
                $ServerCurrency = new \x\rpc\ServerCurrency();
                $ServerCurrency->returnJson(
                    $this->server,  
                    $this->fd, 
                    '200', 
                    'middleware_error', 
                    $mixed
                );
            break;
            case 'mqtt':
                $data = [
                    'type' => \x\mqtt\common\Types::DISCONNECT,
                    'msg' => $mixed,
                ];
                $arr = $this->server->fds[$this->fd];
                $class = $arr['class'];
                $this->server->send($this->fd, $class::pack($data));
            break;
        }
        return false;
    }
}