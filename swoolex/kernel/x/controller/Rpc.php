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
    public $headers = [];
    /**
     * 请求参数
    */
    public $param = [];
    /**
     * 当次请求处理说明
    */
    public $msg = 'SUCCESS';
    /**
     * 当次请求处理业务是否异常
    */
    public $rpc_error = false;
    /**
     * 当次请求处理业务异常的说明
    */
    public $rpc_msg = '';

    /**
     * 输出返回内容给客户端
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @param mixed $data 返回内容
     * @param mixed $msg 处理说明
     * @return mixed
    */
    public final function fetch($data, $msg=null) {
        if ($msg !== null) $this->msg = $msg;
        return $data;
    }

    /**
     * 获取请求头
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @return array
    */
    public final function headers() {
        return $this->headers;
    }

    /**
     * 获取请求参数
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @return array
    */
    public final function param() {
        return $this->param;
    }

    /**
     * 设置当次请求处理说明
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-20
     * @return bool true
    */
    public final function msg($msg) {
        $this->msg = $msg;
        return true;
    }

    /**
     * 标记当次请求业务处理异常
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-24
     * @param string $msg 说明
     * @return bool
    */
    public final function error($msg=null) {
        $this->rpc_error = true;
        if ($msg !== null) {
            $this->rpc_msg = $msg;
        }
        return true;
    }
}