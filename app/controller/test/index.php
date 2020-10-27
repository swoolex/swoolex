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

namespace app\controller\test;
use x\Controller;

/**
 * @Test(msg="我是自定义的注解")
 * @Controller(prefix="ceshi")
*/
class index extends Controller
{
    public static $all;

    /**
     * @Test(msg="我是自定义的注解")
     * @RequestMapping(route="/index", method="GET", title="测试文件上传")
    */
    public function A() {
        $res = \x\Session::set('test', ['name' => '小黄牛']);
        return $this->fetch(dd($res));
    }
    
    /**
     * @RequestMapping(route="/img", method="GET")
     * onRoute
    */
    public function B() {
        $res = \x\Session::get('test');
        return $this->fetch(dd($res));
    }
    
    /**
     * @RequestMapping(route="/demo", method="GET")
     * onRoute
    */
    public function F() {
        $res = \x\Session::clear();
        return $this->fetch(dd($res));
    }

    /**
     * @RequestMapping(route="/delete", method="GET")
     * onRoute
    */
    public function C() {
        $res = \x\Session::delete('test');
        return $this->fetch(dd($res));
    }

    /**
     * @RequestMapping(route="/vif", method="GET")
     * onRoute
    */
    public function D() {
        $this->verify();
    }
    
    /**
     * @RequestMapping(route="/vif2", method="GET")
     * onRoute
    */
    public function E() {
        $data = $this->get();
        return $this->fetch(dd($this->verify_check($data['name'])));
    }
}

