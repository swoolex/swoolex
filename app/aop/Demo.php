<?php
// +----------------------------------------------------------------------
// | 测试AOP
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\aop;

class Demo
{
    //aop 除了异常通知，其余AOP事件都需要return true程序才会向下执行，否则会抛出异常
    //aop 都需要接收以下参数格式

    // 前置
    public function before() {
        return true;
    }
    // 后置
    public function after() {
        return true;
    }
    // 环绕
    public function around() {
        return true;
    }
    // 异常通知
    public function throws($error) {

    }

}