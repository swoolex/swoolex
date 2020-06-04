<?php
// +----------------------------------------------------------------------
// | var_dump美化
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Dump
{
    /**
     * @param $value
     * @throws Exception
     */
    protected function parse($value)
    {
        $type = ucfirst(gettype($value));
        switch ($type) {
            case 'Array': $class = 'x\dd\render\Dump'.ucfirst(gettype($value)); break;
            case 'Object': $class = 'x\dd\render\Dump'.ucfirst(gettype($value)); break;
            default:
                $class = 'x\dd\render\DumpString';
            break;
        }
        
        $obj = new $class($value);
        return $obj->render();
    }

    /**
     * @param $value
     */
    public function run($value)
    {
        return $this->parse($value);
    }
}