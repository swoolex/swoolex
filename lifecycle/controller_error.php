<?php
// +----------------------------------------------------------------------
// | 当应用层捕捉到错误时，系统回调处理的生命周期
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace lifecycle;

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
        $type = \x\Config::run()->get('server.sw_service_type');
        // HTTP请求
        if ($type == 'http') {
            // 开启调试模式则记录错误日志
            if (\x\Config::run()->get('app.de_bug') == true) {
                # 引入详细报错页面
                $exceptionFile = ROOT_PATH.'/swoolex/tpl/error_test.php';
            } else {
                # 引入简单报错页面
                $exceptionFile = ROOT_PATH.'/swoolex/tpl/error_formal.php';
            }

            // 引入文件
            ob_start();
            include $exceptionFile;
            $html = ob_get_clean();

            $obj = new \x\Controller();
            $obj->fetch($html);
        // websocket请求
        } else  if($type == 'websocket') {
            $obj = new \x\WebSocket();
            $obj->fetch('route_error', 'error', $error);
        }
        unset($obj);
        return true;
    }
}