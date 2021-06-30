<?php
/**
 * +----------------------------------------------------------------------
 * 示例控制器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace app\controller;
use x\Controller;

/**
 * @Controller(prefix="")
*/
class Index extends Controller
{
    /**
     * @RequestMapping(route="/", method="get", title="主页")
    */
    public function index() {
        return $this->fetch('<html><meta charset="utf-8"><body style="text-align: center;"><h1 style="padding-top: 20%;font-size: 60px;color: #3674ff;margin: 0;">SW-X</h1><h2 style="font-size: 60px;color: #6f6f6f; margin: 0;">Hello Word！</h2><h3 style="color: #8c8c8c;font-weight: 500;">官网：<a href="https://www.sw-x.cn" style="color: #8c8c8c;">www.sw-x.cn</a></h3></body></html>');
    }
}