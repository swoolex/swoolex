<?php
// +----------------------------------------------------------------------
// | array类型
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\dd\render;
use x\dd\render\AbstractDump;

class DumpArray extends AbstractDump
{
    /**
     * @var array
     */
    public $_stack;

    /**
     * DumpArray constructor.
     * @param $value
     */
    public function __construct($value)
    {
        parent::__construct($value);
    }

    /**
     *
     */
    public function render()
    {
        return $this->display($this->parseArr($this->value));
    }
}