<?php
// +----------------------------------------------------------------------
// | 单元测试基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\doc\lable;

abstract class TestBasics
{
    /**
     * 必须要实现的抽象
    */
    abstract public function getData() : array; // 返回请求数据结构
    abstract public function getHeaders() : array; // 返回请求头

}