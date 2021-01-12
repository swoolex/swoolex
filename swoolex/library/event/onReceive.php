<?php
// +----------------------------------------------------------------------
// | 接收到数据时
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event;

class onReceive
{
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
            $this->server = $server;
            // 微服务
            if (\x\Config::run()->get('server.sw_service_type') == 'rpc') {
                $this->rpc($server, $fd, $reactorId, $data);
            } else {
                $this->server($server, $fd, $reactorId, $data);
            }
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
        \x\Container::getInstance()->set('server', $server);
        \x\Container::getInstance()->set('reactorId', $reactorId);
        // 数据解密
        if (\x\Config::run()->get('rpc.aes_status') == true) {
            $Currency = new \x\rpc\Currency();
            $data = $Currency->aes_decrypt($data);
            unset($Currency);
        }
        $data = json_decode($data, true);

        # 开始转发路由
        $obj = new \x\rpc\ServerRoute();
        $obj->start($server, $fd, $reactorId, $data);

        // 销毁整个请求级容器
        \x\Container::getInstance()->clear();
    }

    /**
     * 普通TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function server($server, $fd, $reactorId, $data) {
        // 调用二次转发，不做重载
        $on = new \app\event\onReceive;
        $on->run($server, $fd, $reactorId, $data);
    }
}

