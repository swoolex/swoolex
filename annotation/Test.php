<?php
// +----------------------------------------------------------------------
// | Test自定义注解-所有注解类都应该继承Basics基类，并实现run接口
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace annotation;
use \x\doc\lable\Basics;

class Test extends Basics
{
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.10 + 2020.07.30
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @param type int 路由类型 1.控制器注解 2.操作方法注解
     * @return bool 返回true表示继续向下执行
    */
    public function run($route, $type){
        // $route是多维数组
        // 当同一注释中，多次声明同一个注解时，只会回调一次，多次参数分别存放在该数组中
        var_dump($route);
        var_dump($type);

        // return route_error函数抛出自定义错误异常
        return $this->route_error('Msg内容自己随便写啦');

        // 若注解通过，应该调用_return()函数，代替return true;
        return $this->_return();
    }
}