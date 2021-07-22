<?php
/**
 * +----------------------------------------------------------------------
 * AopAfter注解解析类
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

class AopAfter extends Basics
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
        
        # 循环注入父AOP事件
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                // 后置操作
                if ($key == 'AopAfter') {
                    if (empty($val['class'])) continue;
                    if (empty($val['function'])) $val['function'] = 'run';

                    $ref = new \ReflectionClass($val['class']);
                    $obj = $ref->newInstance(); 
                    $in_method = $ref->getmethod($val['function']); 
                    
                    $return  = $in_method->invokeArgs($obj, []);
                    if ($return !== true) {
                        return $this->route_error('Father AopBefore');
                    }
                }
            }
        }

        # 循环注入子AOP事件
        if (isset($route['own'])) {
            foreach ($route['own'] as $key=>$val) {
                 // 后置操作
                 if ($key == 'AopAfter') {
                    if (empty($val['class'])) continue;
                    if (empty($val['function'])) $val['function'] = 'run';

                    $ref = new \ReflectionClass($val['class']);
                    $obj = $ref->newInstance(); 
                    $in_method = $ref->getmethod($val['function']); 
                    
                    $return  = $in_method->invokeArgs($obj, []);
                    if ($return !== true) {
                        return $this->route_error('Own AopBefore');
                    }
                }
            }
        }

        // 更新容器
        return $this->_return();
    }

}