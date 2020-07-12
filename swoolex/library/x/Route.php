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
     * WEBSOCKET-server
    */
    private $server;
    /**
     * frame
    */
    private $frame;
    /**
     * WEBSOCKET-HTTP请求对象
    */
    private $request;
    /**
     * HTTP响应对象
    */
    private $response;
    /**
     * 服务类型
    */
    private $service_type;
    /**
     * SessionID名
    */
    private $session_name = 'PHPSESSID';

    /**
     * 接收HTTP请求参数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @param Swoole\Http\Response $response HTTP响应对象
     * @param Swoole\WebSocket\Server $server
     * @param Swoole\WebSocket\Frames $frame 状态信息
     * @return void
    */
    public function __construct($request, $response, $server=null, $frame=null) {
        $this->request = $request;
        $this->response = $response;
        $this->server = $server;
        $this->frame = $frame;

        if ($server) {
            $this->service_type = 'websocket';
        } else {
            $this->service_type = 'http';
        }
    }
    
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return App
    */
    public function start(){
        if ($this->service_type == 'http') {
            $pattern = Config::run()->get('route.pattern');
            $request_uri = $this->format($this->request->server['request_uri']);
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'http');
        } else {
            $data = \x\WebSocket::get_data($this->frame->data);
            $request_uri = $data['action'];
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'websocket');
        }
        
        // 匹配不到
        if ($route == false) {
            if ($this->service_type == 'http') {
                // 实例化基类控制器
                $controller = new \x\Controller();
                $controller->setRequest($this->request);
                $controller->setResponse($this->response);

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
                $obj->setServer($this->server);
                $obj->setFrame($this->frame);
                $obj->fetch('error', '500', $request_uri.' 路由不存在~~');
            }
        } else {
            // 开始解析路由
            if ($this->service_type == 'http') {
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
        if (!isset($this->request->cookie[$this->session_name])) {
            $config = \x\Config::run()->get('app');
            $session_id = session_create_id();
            $this->request->cookie[$this->session_name] = $session_id;
            $this->response->cookie($this->session_name, $session_id, 0, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }
    }

    /**
     * 容器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 被找到的路由
     * @return void
    */
    private function ico_injection($route) {
        $reflection = new \ReflectionClass($route['n']);
        $instance = $reflection->newInstance();
        $method = $reflection->getmethod($route['name']);

        if ($this->service_type == 'http') {
            # 注入Cookies
            \x\Cookie::setRequest($this->request);
            \x\Cookie::setResponse($this->response);
            # 注入Session
            \x\Session::setRequest($this->request);
            \x\Session::setResponse($this->response);
        }

        # 注解参数检测
        if (isset($route['own']['Param'])) {
            $get_list = $this->request->get;
            $post_list = $this->request->post;

            foreach ($route['own']['Param'] as $val) {
                if (empty($val['name'])) continue;
                $name = $val['name'];
                // 获取回调事件名称
                $callback = '';
                if (!empty($val['callback'])) {
                    $callback = $val['callback'];
                }

                // 提示内容
                $tips = $val['tips']??'';

                // 先获取参数
                $param = $get_list[$name]??'';
                if (!$param) $param = $post_list[$name]??'';

                // 参数预设
                if (!empty($val['value']) && empty($param)) {
                    $param = $val['value'];
                    $this->request->get[$name] = $val['value'];
                    $this->request->post[$name] = $val['value'];
                }

                // 判断是否允许为空
                $null = false;
                if (!empty($val['empty']) && $val['empty'] == 'true') {
                    $null = true;
                }
                // 不允许为空
                if ($null && empty($param)) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'NULL');
                }

                // 类型判断
                if (!empty($val['type']) && !empty($param)) {
                    $param_type = explode('|', $val['type']);
                    $param_status = false;
                    $attach = '';
                    foreach ($param_type as $v) {
                        $is = 'is_'.$v;
                        if ($is($param)) {
                            $param_status = true;
                        } else {
                            $attach .= $is.'、';
                        }
                    }
                    // 全都没通过
                    if ($param_status === false) {
                        // 中断
                        return $this->param_error_callback($callback, $tips, $name, 'TYPE', rtrim($attach, '、'));
                    }
                }

                // 长度判断
                $chinese = false;
                if (!empty($val['chinese']) && $val['chinese'] == 'true') {
                    $chinese = true;
                }
                if ($chinese) {
                    $length = mb_strlen($param, 'UTF8'); 
                } else {
                    $length = strlen($param); 
                }
                // 最小长度判断
                if (!empty($val['min']) && $val['min'] > $length) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'MIN');
                }
                // 最大长度判断
                if (!empty($val['max']) && $val['max'] < $length) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'MAX');
                }
                // 正则判断regular
                if (!empty($val['regular']) && !preg_match($val['regular'], $param)) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'REGULAR', $val['regular']);
                }
            }
        }
        
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
                        if ($this->service_type == 'http') {
                            $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
                            throw new \Exception("Route：Father AopBefore Must have return value~");
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        if ($this->service_type == 'http') {
                            $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
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
                        if ($this->service_type == 'http') {
                            $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
                            throw new \Exception("Route：Own AopBefore Must have return value~");
                        }
                    break;
                    case 'AopAround': // 环绕操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        if ($this->service_type == 'http') {
                            $return = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
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
                    if ($this->service_type == 'http') {
                        $in_method->invokeArgs($aop, [$this->request, $this->response, $e]);
                    } else {
                        $in_method->invokeArgs($aop, [$this->server, $this->frame, $e]);
                    }
                }
                if ($own_AopThrows) {
                    $ref = new \ReflectionClass($own_AopThrows['class']);
                    $aop = $ref->newInstance(); 
                    $in_method = $ref->getmethod($own_AopThrows['function']); 
                    if ($this->service_type == 'http') {
                        $in_method->invokeArgs($aop, [$this->request, $this->response, $e]);
                    } else {
                        $in_method->invokeArgs($aop, [$this->server, $this->frame, $e]);
                    }
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
                        if ($this->service_type == 'http') {
                            $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return  = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
                            throw new \Exception("Route：Father AopAround Must have return value~");
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        if ($this->service_type == 'http') {
                            $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return  = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
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
                        if ($this->service_type == 'http') {
                            $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return  = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
                            throw new \Exception("Route：Own AopAround Must have return value~");
                        }
                    break;
                    case 'AopAfter': // 后置操作
                        if (empty($val['class'])) break;
                        if (empty($val['function'])) $val['function'] = 'run';

                        $ref = new \ReflectionClass($val['class']);
                        $obj = $ref->newInstance(); 
                        $in_method = $ref->getmethod($val['function']); 
                        if ($this->service_type == 'http') {
                            $return  = $in_method->invokeArgs($obj, [$this->request, $this->response]);
                        } else {
                            $return  = $in_method->invokeArgs($obj, [$this->server, $this->frame]);
                        }
                        if ($return !== true) {
                            throw new \Exception("Route：Father AopBefore Must have return value~");
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

    /**
     * 当注解Param检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 回调事件
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return void
    */
    private function param_error_callback($callback, $tips, $name, $status, $attach=null) {
        // 如果不定义回调事件，则启用系统的生命周期回调处理
        if (empty($callback)) {
            $callback = '\lifecycle\\annotate_param';
        }
        // 判断注入的请求对象类型
        if ($this->service_type == 'http') {
            $request = $this->request;
            $response = $this->response;
        } else {
            $request = $this->server;
            $response = $this->frame;
        }
        // 判断回调事件是类，还是函数
        if (stripos($callback, '\\') !== false) {
            $obj = new $callback;
            return $obj->run($request, $response, $this->service_type, $tips, $name, $status, $attach);
        } else {
            return $callback($request, $response, $this->service_type, $tips, $name, $status, $attach);
        }
    }
}