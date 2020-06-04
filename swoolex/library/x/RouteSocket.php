<?php
// +----------------------------------------------------------------------
// | WebSocket路由类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class RouteSocket
{
    /**
     * server
    */
    private $server;
    /**
     * frame
    */
    private $frame;

    /**
     * 接收HTTP请求参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\WebSocket\Server $server
     * @param Swoole\WebSocket\Frames $frame 状态信息
     * @return void
    */
    public function __construct($server, $frame) {
        $this->server = $server;
        $this->frame = $frame;
    }
    
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return App
    */
    public function start(){
        $data = \x\WebSocket::get_data($this->frame->data);
        $request_uri = $data['action'];

        # 先匹配出路由
        $route = \x\route\WebSocket::run()->get($request_uri);

        # 匹配不到
        if ($route == false) {
            $obj = new \x\WebSocket();
            $obj->setServer($this->server);
            $obj->setFrame($this->frame);
            $obj->fetch('error', '500', $request_uri.' 路由不存在~~');
        } else {
            # 开始解析路由
            $this->ico_injection($route);
        }
    }

    /**
     * 容器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 被找到的路由
     * @return void
    */
    private function ico_injection($route) {
        $reflection = new \ReflectionClass($route['n']);
        $instance = $reflection->newInstance();
        $method = $reflection->getmethod($route['name']);

        # 先注入请求和响应
        $obj = $reflection->getmethod('setServer');
        $obj->invokeArgs($instance, [$this->server]);
        $obj = $reflection->getmethod('setFrame');
        $obj->invokeArgs($instance, [$this->frame]);

        # 检测路由类型
        if (isset($route['method'])) {
            $http_type = explode('|', strtoupper($route['method']));
            $status = false;
            foreach ($http_type as $v) {
                if ($v == $this->request->server['request_method']) {
                    $status = true;
                }
            }
            if ($status == false) {
                $fetch = $reflection->getmethod('fetch');
                return $fetch->invokeArgs($instance, [\x\Lang::run()->get('route method error'), '500']);
            }
        }

        # 注入Cookies
        \x\Cookie::setRequest($this->request);
        \x\Cookie::setResponse($this->response);
        # 注入Session
        \x\Session::setRequest($this->request);
        \x\Session::setResponse($this->response);

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
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Father AopBefore Must have return value~");
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Father AopAround Must have return value~");
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
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Own AopBefore Must have return value~");
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Own AopAround Must have return value~");
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
        
        # 载入控制器
        if ($father_AopThrows && $own_AopThrows) {
            try{
                $method->invokeArgs($instance, []);
            } catch(\Exception $e) {
                // 开始异常通知
                if ($father_AopThrows) {
                    $ref = new \ReflectionClass($father_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($father_AopThrows['function']); 
                    $in_method->invokeArgs($aop, [$this->server, $this->frame, $e]);
                }
                if ($own_AopThrows) {
                    $ref = new \ReflectionClass($own_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($own_AopThrows['function']); 
                    $in_method->invokeArgs($aop, [$this->server, $this->frame, $e]);
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
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Father AopAround Must have return value~");
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Father AopBefore Must have return value~");
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
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Own AopAround Must have return value~");
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        if (!$return) {
                            throw new \Exception("Route：Father AopBefore Must have return value~");
                        }
                    break;
                }
            }
        }
    }

}