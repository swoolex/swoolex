<?php
// +----------------------------------------------------------------------
// | SPAN标签
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\dd\decorator;

class Span extends DecoratorComponent
{

    /**
     * @param array $params
     * @return $this
     */
    public function wrap(Array $params = [])
    {
        $params = empty($params) ? $params : array_pop($params);
        $this->value = (array_key_exists('withQuota', $params) && !$params['withQuota']) ? $this->noWrap($this->value) : $this->withQuota($this->noWrap($this->value));
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