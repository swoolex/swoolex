<?php
/**
 * +----------------------------------------------------------------------
 * AopThrows注解解析类
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

class AopThrows extends Basics
{
    /**
     * 启动项
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.18
     * @param array $route 路由参数
     * @param bool $tcp_status 是否tcp的服务
     * @return true
    */
    public function run($route, $tcp_status=false){
        # 循环注入父AOP事件
        $father_AopThrows = '';
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                // 异常操作
                if ($key == 'AopThrows') {
                    if (empty($val['class'])) continue;
                    if (empty($val['function'])) $val['function'] = 'run';

                    $father_AopThrows = $val;
                }
            }
        }
        
        # 循环注入子AOP事件
        $own_AopThrows = '';
        if (isset($route['own'])) {
            foreach ($route['own'] as $key=>$val) {
                // 异常操作
                if ($key == 'AopThrows') {
                    if (empty($val['class'])) continue;
                    if (empty($val['function'])) $val['function'] = 'run';

                    $own_AopThrows = $val;
                }
            }
        }

        
        # 载入控制器
        $ref = \x\context\Container::get('controller_reflection');
        if ($father_AopThrows || $own_AopThrows) {
            try{
                if ($ref->hasMethod('__initialize')) {
                    $method = $ref->getmethod('__initialize'); 
                    $method->invokeArgs($this->controller_instance, []);
                }
                $return = $this->controller_method->invokeArgs($this->controller_instance, []);
            } catch(\Exception $e) {
                // 开始异常通知
                if ($father_AopThrows) {
                    $ref = new \ReflectionClass($father_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($father_AopThrows['function']); 
                    
                    $in_method->invokeArgs($aop, [$e]);
                }
                if ($own_AopThrows) {
                    $ref = new \ReflectionClass($own_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($own_AopThrows['function']); 

                    $in_method->invokeArgs($aop, [$e]);
                }
            }
        } else {
            if ($ref->hasMethod('__initialize')) {
                $method = $ref->getmethod('__initialize'); 
                $method->invokeArgs($this->controller_instance, []);
            }
            $return = $this->controller_method->invokeArgs($this->controller_instance, []); 
        }
        
        // 兼容HTTP/WebSocket服务
        if ($tcp_status === false) {
            $return = true;
        }
        // 更新容器
        return $this->_return($return);
    }

}