<?php
/**
 * +----------------------------------------------------------------------
 * Ioc注解解析类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route\doc\lable;
use \x\route\doc\lable\Basics;

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
        // HTTP请求
        if (!empty($this->request->server['request_method'])) {
            if ($this->request->server['request_method'] == 'GET') {
                $param = $this->request->get;
            } else {
                $param = $this->request->post;
            }
            // 收到了单元测试发起请求
            if (!empty($param['SwooleXTestCase']) && $param['SwooleXTestCase'] == 1) {
                // 更新容器
                return $this->_return();
            }
        }

        # 循环注入父容器
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                if ($key == 'Ioc') {
                    foreach ($val as $v) {
                        $name = $v['name'];

                        $length = strpos($v['class'], '(');
                        // 类命名空间地址
                        $class = substr($v['class'], 0, $length);
                        // 构造方法参数
                        $class_args = str_replace(["' ,", "', "], "',", substr($v['class'], $length+1, strlen($v['class'])-($length+2)));
                        
                        if ($length > 1 && !empty($class_args)) {
                            $_arr = explode("',", $class_args);
                            $args_arr = [];
                            foreach ($_arr as $v) {
                                $args_arr[] = rtrim(ltrim($v, "'"), "'");
                            }
                            $in_reflection = new \reflectionClass($class);
                            $obj = $in_reflection->newInstanceArgs($args_arr); 
                        } else {
                            $class = str_replace([' ', '()'], '', $v['class']);
                            $in_reflection = new \reflectionClass($class);
                            $obj = $in_reflection->newInstance(); 
                        }

                        // 执行方法
                        if (empty($v['function'])) {
                            if ($this->controller_method->isStatic()) {
                                return $this->route_error('Ioc Static');
                            }
                            $this->controller_instance->$name = $obj;
                        } else {
                            $length = strpos($v['function'], '(');
                            $function = substr($v['function'], 0, $length);
                            $function_args = str_replace(["' ,", "', "], "',", substr($v['function'], $length+1, strlen($v['function'])-($length+2)));
                            $args_arr = [];
                            if ($length > 1 && !empty($function_args)) {
                                $_arr = explode("',", $function_args);
                                foreach ($_arr as $v) {
                                    $args_arr[] = rtrim(ltrim($v, "'"), "'");
                                }
                            } else {
                                $function = str_replace([' ', '()'], '', $v['function']);
                            }
                            $in_method = $in_reflection->getmethod($function);
                            $this->controller_instance->$name = $in_method->invokeArgs($obj, $args_arr);
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
                        $name = $v['name'];
                        $length = strpos($v['class'], '(');
                        // 类命名空间地址
                        $class = substr($v['class'], 0, $length);
                        // 构造方法参数
                        $class_args = str_replace(["' ,", "', "], "',", substr($v['class'], $length+1, strlen($v['class'])-($length+2)));
                        if ($length > 1 && !empty($class_args)) {
                            $_arr = explode("',", $class_args);
                            $args_arr = [];
                            foreach ($_arr as $v) {
                                $args_arr[] = rtrim(ltrim($v, "'"), "'");
                            }
                            $in_reflection = new \reflectionClass($class);
                            $obj = $in_reflection->newInstanceArgs($args_arr); 
                        } else {
                            $class = str_replace([' ', '()'], '', $v['class']);
                            $in_reflection = new \reflectionClass($class);
                            $obj = $in_reflection->newInstance(); 
                        }

                        // 执行方法
                        if (empty($v['function'])) {
                            if ($this->controller_method->isStatic()) {
                                return $this->route_error('Ioc Static');
                            }
                            $this->controller_instance->$name = $obj;
                        } else {
                            $length = strpos($v['function'], '(');
                            $function = substr($v['function'], 0, $length);
                            $function_args = str_replace(["' ,", "', "], "',", substr($v['function'], $length+1, strlen($v['function'])-($length+2)));
                            $args_arr = [];
                            if ($length > 1 && !empty($function_args)) {
                                $_arr = explode("',", $function_args);
                                foreach ($_arr as $v) {
                                    $args_arr[] = rtrim(ltrim($v, "'"), "'");
                                }
                            } else {
                                $function = str_replace([' ', '()'], '', $v['function']);
                            }
                            $in_method = $in_reflection->getmethod($function);
                            $this->controller_instance->$name = $in_method->invokeArgs($obj, $args_arr);
                        }
                    }
                }
            }
        }

        // 更新容器
        return $this->_return();
    }

}