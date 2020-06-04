<?php
// +----------------------------------------------------------------------
// | DIV标签
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\dd\decorator;

class Div extends DecoratorComponent
{

    /**
     * @return $this
     */
    public function wrap()
    {
        $this->value = $this->noWrap($this->value);
        return $this;
    }

    /**
     *
     */
    public function display()
    {
        return $this->initStyle().$this->value;
    }
}