<?php
// +----------------------------------------------------------------------
// | Restful类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

use x\Facade;

class Restful extends Facade
{
    /**
     * 返回格式
    */
    public $type;
    /**
     * 返回结构
    */
    public $make;
    /**
     * 状态码
    */
    public $code;
    /**
     * Tips
    */
    public $msg;
    /**
     * 结果集
    */
    public $data;
    /**
     * 返回值结构
    */
    public $structure;
}