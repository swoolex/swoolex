<?php
// +----------------------------------------------------------------------
// | 路由类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Route
{
    /**
     * SessionID名
    */
    private $session_name = 'PHPSESSID';
    
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.18
     * @deprecated 暂不启用
     * @global 无
     * @return App
    */
    public function start(){
        // 获取容器
        $request = \x\Container::getInstance()->get('request');
        if ($request) {
            $pattern = Config::run()->get('route.pattern');
            $request_uri = $this->format($request->server['request_uri']);
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'http');
        } else {
            $data = \x\WebSocket::get_data();
            $request_uri = $data['action'];
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'websocket');
        }

        // 匹配不到
        if ($route == false) {
            if ($request) {
                // 实例化基类控制器
                $controller = new \x\Controller();
                $class = \x\Config::run()->get('route.error_class');
                // 系统404
                if (\x\Config::run()->get('route.404') == false || empty($class)) {
                    $controller->fetch(\x\Lang::run()->get('route path error'), '404');
                } else {
                // 自定义404
                    new $class($controller);
                }
            } else {
                $obj = new \x\WebSocket();
                $obj->fetch('error', '500', $request_uri.' 路由不存在~~');
            }
        } else {
            // 开始解析路由
            if ($request) {
                $this->session();
            }
            $this->ico_injection($route);
        }
    }

    /**
     * Session注入
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function session() {
        // 获取容器
        $request = \x\Container::getInstance()->get('request');
        $response = \x\Container::getInstance()->get('response');

        if (!isset($request->cookie[$this->session_name])) {
            $config = \x\Config::run()->get('app');
            $session_id = session_create_id();
            $request->cookie[$this->session_name] = $session_id;
            $response->cookie($this->session_name, $session_id, 0, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }

        // 更新容器
        \x\Container::getInstance()->set('request', $request);
        \x\Container::getInstance()->set('response', $response);
    }

    /**
     * 容器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.1.8 + 2020.07.6
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 被找到的路由
     * @return void
    */
    private function ico_injection($route) {
        // 注册注解类

        // 参数过滤
        $route = (new \x\doc\lable\Param())->run($route);
        if ($ret !== true) return $ret;
        // // 容器
        // $ret = (new \x\doc\lable\Ioc())->run($route);
        // if ($ret !== true) return $ret;
        // // 前置操作
        // $ret = (new \x\doc\lable\AopBefore())->run($route);
        // if ($ret !== true) return $ret;
        // // 环绕操作
        // $ret = (new \x\doc\lable\AopAround())->run($route, 1);
        // if ($ret !== true) return $ret;
        // // 异常操作
        // $ret = (new \x\doc\lable\AopThrows())->run($route);
        // if ($ret !== true) return $ret;
        // // 后置操作
        // $ret = (new \x\doc\lable\AopAfter())->run($route);
        // if ($ret !== true) return $ret;
        // // 环绕操作
        // $ret = (new \x\doc\lable\AopAround())->run($route, 2);
        // if ($ret !== true) return $ret;

        return false;

        # 循环注入父容器
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                if ($key == 'Ioc') {
                    unset($route['father'][$key]);

                    foreach ($val as $v) {
                        $args = [];
                        if (!empty($v['args'])) {
                            $args = $v['args'];
                        }

                        $name = $v['name'];
                        $in_reflection = new \ReflectionClass($v['class']);
                        $obj = $in_reflection->newInstance(); 

                        # 动态属性注入
                        if (!$method->isStatic()) {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($v['function']);
                                $instance->$name = $in_method->invokeArgs($obj, $args);
                            } else {
                                $instance->$name = $obj;
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
                    unset($route['own'][$key]);

                    foreach ($val as $v) {
                        $args = [];
                        if (!empty($v['args'])) {
                            $args = $v['args'];
                        }

                        $name = $v['name'];
                        $in_reflection = new \ReflectionClass($v['class']);
                        $obj = $in_reflection->newInstance(); 

                        # 动态属性注入
                        if (!$method->isStatic()) {
                            if (!empty($v['function'])) {
                                $in_method = $in_reflection->getmethod($v['function']);
                                $instance->$name = $in_method->invokeArgs($obj, $args);
                            } else {
                                $instance->$name = $obj;
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

        # 循环注入父AOP事件
        $father_AopThrows = '';
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                switch ($key) {
                    case 'AopBefore': // 前置操作
                        unset($route['father'][$key]);
                        
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 

                        $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Father AopBefore');
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Father AopAround');
                        }
                    break;
                    case 'AopThrows': // 异常操作
                        unset($route['father'][$key]);

                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $father_AopThrows = $val;
                    break;
                }
            }
        }
        
        # 循环注入子AOP事件
        $own_AopThrows = '';
        if (isset($route['own'])) {
            foreach ($route['own'] as $key=>$val) {
                switch ($key) {
                    case 'AopBefore': // 前置操作
                        unset($route['own'][$key]);

                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Own AopBefore');
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Own AopAround');
                        }
                    break;
                    case 'AopThrows': // 异常操作
                        unset($route['own'][$key]);

                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $own_AopThrows = $val;
                    break;
                }
            }
        }

        # 先注入请求和响应
        if ($this->service_type == 'http') {
            $obj = $reflection->getmethod('setRequest');
            $obj->invokeArgs($instance, [$this->request]);
            $obj = $reflection->getmethod('setResponse');
            $obj->invokeArgs($instance, [$this->response]);
        } else {
            $obj = $reflection->getmethod('setServer');
            $obj->invokeArgs($instance, [$this->server]);
            $obj = $reflection->getmethod('setFrame');
            $obj->invokeArgs($instance, [$this->frame]);
        }
        
        # 载入控制器
        if ($father_AopThrows || $own_AopThrows) {
            try{
                $method->invokeArgs($instance, []);
            } catch(\Exception $e) {
                // 开始异常通知
                if ($father_AopThrows) {
                    $ref = new \ReflectionClass($father_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($father_AopThrows['function']); 
                    
                    $in_method->invokeArgs($aop, [$this->request, $this->response, $e]);
                }
                if ($own_AopThrows) {
                    $ref = new \ReflectionClass($own_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($own_AopThrows['function']); 

                    $in_method->invokeArgs($aop, [$this->request, $this->response, $e]);
                }
            }
        } else {
            $method->invokeArgs($instance, []); 
        }

        # 循环注入父AOP事件
        if (isset($route['father'])) {
            foreach ($route['father'] as $key=>$val) {
                switch ($key) {
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 

                        $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Father AopAround');
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Father AopBefore');
                        }
                    break;
                }
            }
        }

        # 循环注入子AOP事件
        if (isset($route['own'])) {
            foreach ($route['own'] as $key=>$val) {
                switch ($key) {
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Own AopAround');
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        
                        $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        if ($return !== true) {
                            return $this->route_error('Own AopBefore');
                        }
                    break;
                }
            }
        }
    }

    /**
     * 清除URL格式
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param string $request_uri
     * @return string
    */
    private function format($request_uri) {
        $array = explode(\x\Config::run()->get('route.suffix'), $request_uri);
        $url = ltrim(strtolower($array[0]), '/');
        $filter = [
            'index',
            'index.html',
            'index.php',
        ];
        if (empty($url) || in_array($url, $filter)) {
            $url = '/';
        }
        return $url;
    }
}