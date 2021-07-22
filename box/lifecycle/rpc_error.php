<?php
/**
 * +----------------------------------------------------------------------
 * 当客户端微服务失败时，回调的处理函数
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class rpc_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $class 请求地址 
     * @param string $function 请求方法
     * @param array $config 错误的微服务配置 
     * @param int $status 错误类型 
     *            1.ping的linux指令不能正常使用
     *            2.ping不通
     * @return bool
    */
    public function run($class, $function, $config, $status) {
        
        // 此处可自行实现消息通知
        echo '微服务：'.$class.' '.$function.' 错误，事件编号：'.$status.PHP_EOL;
        return true;
    }
}