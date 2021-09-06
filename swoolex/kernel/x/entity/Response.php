<?php
/**
 * +----------------------------------------------------------------------
 * HTTP请求对象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;
use design\AbstractSingleCase;

class Response
{
    use AbstractSingleCase;

    /**
     * 请求实例
    */
    private $response;

    /**
     * 设置实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Response $response HTTP请求对象
     * @return void
    */
    public function set($response) {
        $this->response = $response;
    }

    /**
     * 获取实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return Swoole\Http\Response
    */
    public function get() {
        return $this->response;
    }
}