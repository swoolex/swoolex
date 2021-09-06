<?php
/**
 * +----------------------------------------------------------------------
 * 路由限流器达到峰值时，回调的通知函数
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class limit_route_check
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型 http/websocket/rpc
     * @param string $route 触发路由
     * @param string $data 对应限流配置信息
     * @return void
    */
    public function run($server_type, $route, $data) {
        switch ($server_type) {
            case 'http':
                
            break;
            case 'websocket':
                
            break;
            case 'rpc':
                
            break;
        }
        return true;
    }
}