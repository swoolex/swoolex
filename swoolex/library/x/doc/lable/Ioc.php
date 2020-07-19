<?php
// +----------------------------------------------------------------------
// | Ioc注解解析类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\doc\lable;
use \x\doc\lable\Basics;

class Ioc extends Basics
{
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.18
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @return true
    */
    public function run($route){
        # 循环注入父容器
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                if ($key == 'Ioc') {
                    foreach ($val as $v) {
                        $args = [];
                        if (!empty($v['args'])) {
                            $args = $v['args'];
                        }

                        $name = $v['name'];
                        $in_reflection = new \ReflectionClass($v['class']);
                        $obj = $in_reflection->newInstance(); 

                        # 动态属性注入
                        if (!$this->controller_method->isStatic()) {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($v['function']);
                                $this->controller_instance->$name = $in_method->invokeArgs($obj, $args);
                            } else {
                                $this->controller_instance->$name = $obj;
                            }
                        # 静态属性注入
                        } else {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($value['function']);
                                $reflection->setStaticPropertyValue($name, $in_method->invokeArgs($obj, $args));
                            } else {
                                $reflection->setStaticPropertyValue($name, $obj);
                            }
                        }
                    }
                }
            }
        }

        # 循环注入子注解
        if (isset($route['own'])) {
            foreach ($route['own'] as $key=>$val) {
                if ($key == 'Ioc') {
                    foreach ($val as $v) {
                        $args = [];
                        if (!empty($v['args'])) {
                            $args = $v['args'];
                        }

                        $name = $v['name'];
                        $in_reflection = new \ReflectionClass($v['class']);
                        $obj = $in_reflection->newInstance(); 

                        # 动态属性注入
                        if (!$this->controller_method->isStatic()) {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($v['function']);
                                $this->controller_instance->$name = $in_method->invokeArgs($obj, $args);
                            } else {
                                $this->controller_instance->$name = $obj;
                            }
                        # 静态属性注入
                        } else {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($value['function']);
                                $reflection->setStaticPropertyValue($name, $in_method->invokeArgs($obj, $args));
                            } else {
                                $reflection->setStaticPropertyValue($name, $obj);
                            }
                        }
                    }
                }
            }
        }

        // 更新容器
        return $this->_return();
    }

}