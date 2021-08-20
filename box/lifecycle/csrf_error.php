<?php
/**
 * +----------------------------------------------------------------------
 * 当Csrf注解校验失败时，系统回调处理的生命周期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class csrf_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $tips 错误内容
     * @return bool
    */
    public function run($tips) {
        $type = \x\Config::get('server.sw_service_type');
        // HTTP请求
        if ($type == 'http') {
            $obj = new \x\controller\Http();
            $obj->fetch($tips);
        }
        unset($obj);
        return true;
    }
}