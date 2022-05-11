<?php
/**
 * +----------------------------------------------------------------------
 * 监听WebSokcet握手过程
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\event\server;

class onHandShake
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 接收服务实例
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @param Swoole\Server $server
    */
    public function __construct($server) {
        $this->server = $server;
    }
    
    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @param Swoole\Http\Response $response HTTP响应对象
     * @return void
    */
    public function run($request, $response) {
        
    }
}

