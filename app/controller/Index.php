<?php
// +----------------------------------------------------------------------
// | 控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

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
        return $this->fetch('<html><body style="text-align: center;"><h1 style="padding-top: 20%;font-size: 60px;color: #3674ff;margin: 0;">SW-X</h1><h2 style="font-size: 60px;color: #6f6f6f; margin: 0;">Hello Word！</h2><h3 style="color: #8c8c8c;font-weight: 500;">官网：<a href="https://www.sw-x.cn" style="color: #8c8c8c;">www.sw-x.cn</a></h3></body></html>');
    }

    /**
     * @RequestMapping(route="demo", method="get", title="测试而已")
    */
    public function test() {
        return $this->fetch(dd(\x\Doc\Table::run()->route()));
    }
}