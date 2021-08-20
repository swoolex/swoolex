<?php
/**
 * +----------------------------------------------------------------------
 * RPC服务 - 控制器基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\controller;

class RPC {
    /**
     * 请求头
    */
    private $headers = [];
    /**
     * 请求参数
    */
    private $param = [];
    /**
     * 当次请求处理说明
    */
    private $msg = 'SUCCESS';

    /**
     * 输出返回内容给客户端
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $data 返回内容
     * @param mixed $msg 处理说明
     * @return void
    */
    public final function fetch($data, $msg=null) {
        if ($msg !== null) $this->msg = $msg;
        return $data;
    }

    /**
     * 获取请求头
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public final function headers() {
        return $this->headers;
    }

    /**
     * 获取请求参数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public final function param() {
        return $this->param;
    }

    /**
     * 设置当次请求处理说明
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @deprecated 暂不启用
     * @global 无
     * @return bool true
    */
    public final function msg($msg) {
        $this->msg = $msg;
        return true;
    }
}