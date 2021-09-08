<?php
/**
 * +----------------------------------------------------------------------
 * 接收到数据时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\rpc;
use x\Config;

class onReceive {
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.3 + 2020.07.06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @return void
    */
    public function run($server, $fd, $reactorId, $data=null) {
        try {
            $ip = $server->getClientInfo($fd)['remote_ip'];
            // 触发限流器
            if (\x\Limit::ipVif($server, $fd, $ip, 'rpc') == false) {
                return false;
            }

            $this->server = $server;
            
            // 业务挂载
            $this->rpc($server, $fd, $reactorId, $data);
            
            // 调用二次转发，不做重载
            $on = new \box\event\server\onReceive;
            $on->run($server, $fd, $reactorId, $data);
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }

    /**
     * 微服务TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function rpc($server, $fd, $reactorId, $data) {
        // 请求注入容器
        \x\context\Container::set('server', $server);
        \x\context\Container::set('fd', $fd);
        \x\context\Container::set('reactorId', $reactorId);
        // 数据解密
        if (Config::get('rpc.aes_status') == true) {
            $Currency = new \x\rpc\Currency();
            $data = $Currency->aes_decrypt($data);
            unset($Currency);
        }
        $data = json_decode($data, true);

        if (isset($data['task'])) {
            if ($data['task'] == true) {
                // 投递异步任务
                $data['swoolex_rpc_task'] = 1;
                $task_id = $server->task(json_encode($data, JSON_UNESCAPED_UNICODE));
                // 直接返回结果
                $ServerCurrency = new \x\rpc\ServerCurrency();
                $ret = $ServerCurrency->returnJson($server, $fd, '200', 'SUCCESS', true);
                // 销毁整个请求级容器
                \x\context\Container::delete();
                return $ret;
            }
        }

        # 开始转发路由
        $obj = new \x\rpc\ServerRoute();
        $obj->start($server, $fd, $reactorId, $data);

        // 销毁整个请求级容器
        \x\context\Container::delete();
    }
}

