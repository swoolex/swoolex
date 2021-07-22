<?php
/**
 * +----------------------------------------------------------------------
 * 监听外部调用请求
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event\http;

class onRequest {
    /**
	 * 启动实例
	*/
    public $server;
    /**
	 * 配置项
	*/
    public $config;

    /**
     * 接收服务实例
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param array $config 配置项
     * @param Swoole $server
     * @return void
    */
    public function __construct($server, $config) {
        $this->server = $server;
        $this->config = $config;
    }

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @param Swoole\Http\Response $response HTTP响应对象
     * @return void
    */
    public function run($request, $response) {
        // 业务挂载
        \design\MonitorHttpEvent::start($this->server, $this->config, $request, $response);
    }
}

