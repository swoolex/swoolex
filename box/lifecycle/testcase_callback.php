<?php
/**
 * +----------------------------------------------------------------------
 * 单元测试注解的回调处理
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class testcase_callback
{
    /**
     * 接受回调处理
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @param string $tips 内容
     * @return bool
    */
    public function run($tips) {

        $type = \x\Config::get('server.sw_service_type');
        // HTTP请求
        if ($type == 'http') {
            $obj = new \x\controller\Http();
            $obj->fetch($tips);
        // websocket请求
        } else if($type == 'websocket') {
            $obj = new \x\controller\WebSocket();
            $obj->fetch('route_error', 'error', $tips);
        }
        return true;
    }
}