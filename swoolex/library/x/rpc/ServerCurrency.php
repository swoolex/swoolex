<?php
// +----------------------------------------------------------------------
// | 微服务-服务端通用助手类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed (http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\rpc;

class ServerCurrency
{
    /**
     * 数据返回
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param server $server
     * @param fd $fd
     * @param string $status
     * @param mixed $msg
     * @param array $data
     * @return void
    */
    public function returnJson($server, $fd, $status, $msg="SUCCESS", $data=[]) {
        $json = json_encode([
            'status' => "{$status}",
            'msg' => $msg,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);

        if (\x\Config::run()->get('rpc.aes_status') == true) {
            $Currency = new Currency();
            $json = $Currency->aes_encrypt($json);
            unset($Currency);
        }
        return $server->send($fd, $json);
    }
}