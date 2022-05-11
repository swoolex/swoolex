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

class Request
{
    use AbstractSingleCase;
    
    /**
     * 请求实例
    */
    private $request;

    /**
     * 设置实例
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @param Swoole\Http\Request $request HTTP请求对象
    */
    public function set($request) {
        $this->request = $request;
    }

    /**
     * 获取实例
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @return Swoole\Http\Request
    */
    public function get() {
        return $this->request;
    }
}