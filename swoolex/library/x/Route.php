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
        $request = \x\Container::get('request');
        if ($request) {
            $pattern = \x\Config::get('route.pattern');
            $request_uri = $this->format($request->server['request_uri']);
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'http');
        } else {
            $obj = new \x\WebSocket();
            $data = $obj->get_data();
            $request_uri = $data['action'];
            // 先匹配出路由
            $route = \x\doc\Table::run()->get($request_uri, 'websocket');
        }

        // 匹配不到
        if ($route == false) {
            if ($request) {
                // 实例化基类控制器
                $controller = new \x\Controller();
                $class = \x\Config::get('route.error_class');
                // 系统404
                if (\x\Config::get('route.404') == false || empty($class)) {
                    $controller->fetch(\x\Lang::get('route path error'), '404');
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

            $this->ico_injection($route, $request_uri);
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
        $request = \x\Container::get('request');
        $response = \x\Container::get('response');

        if (!isset($request->cookie[$this->session_name])) {
            $config = \x\Config::get('app');
            $session_id = session_create_id();
            $request->cookie[$this->session_name] = $session_id;
            $response->cookie($this->session_name, $session_id, 0, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }

        // 更新容器
        \x\Container::set('request', $request);
        \x\Container::set('response', $response);
    }

    /**
     * 容器注入
     * @todo 无
     * @author 小黄牛
     * @version v2.0.6 + 2021.04.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 被找到的路由
     * @param string $request_uri 路由地址
     * @return void
    */
    private function ico_injection($route, $request_uri) {
        // 实例化控制器
        $reflection = new \ReflectionClass($route['n']);
        \x\Container::set('controller_instance', $reflection->newInstance());
        \x\Container::set('controller_method', $reflection->getmethod($route['name']));
        // 注册注解类

        // 参数过滤
        $ret = (new \x\doc\lable\Param())->run($route);
        if ($ret !== true) return $ret;
        // Csrf
        $ret = (new \x\doc\lable\Csrf())->run($route);
        if ($ret !== true) return $ret;
        // Jwt
        $ret = (new \x\doc\lable\Jwt())->run($route);
        if ($ret !== true) return $ret;
        // 容器
        $ret = (new \x\doc\lable\Ioc())->run($route);
        if ($ret !== true) return $ret;
        // 单元测试操作
        $ret = (new \x\doc\lable\TestCase())->run($route, $request_uri);
        if ($ret !== true) return $ret;
        // 前置操作
        $ret = (new \x\doc\lable\AopBefore())->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\doc\lable\AopAround())->run($route);
        if ($ret !== true) return $ret;
        // 自定义注解
        $ret = $this->diy_annotation($route);
        if ($ret !== true) return $ret;
        // 异常操作 - 在这里触发控制器
        $ret = (new \x\doc\lable\AopThrows())->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\doc\lable\AopAround())->run($route, 2);
        if ($ret !== true) return $ret;
        // 后置操作
        $ret = (new \x\doc\lable\AopAfter())->run($route);
        if ($ret !== true) return $ret;

        return false;
    }

    /**
     * 自定义注解载入
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 被找到的路由
     * @return bool
    */
    private function diy_annotation($route) {
        /**
         * 内置注解标签大全
        */
        $arr = [
            'RequestMapping',
            'Ioc',
            'AopBefore',
            'AopAfter',
            'AopAround',
            'AopThrows',
            'Param',
            'Controller',
            'onRoute',
            'TestCase'
        ];
        // 注册自定义注解类
        // 控制器注解
        foreach ($route['father'] as $k=>$v) {
            if (in_array($k, $arr) == false) {
                // 自定义注解类地址
                $file = ROOT_PATH.'/other/annotation/'.$k.'.php';
                // 存在则载入
                if (file_exists($file)) {
                    $class = '\other\annotation\\'.$k;
                    $obj = new $class;
                    $ret = $obj->run($v, 1);
                    if ($ret !== true) return $ret;
                }
            }
        }
        // 操作方法注解
        foreach ($route['own'] as $k=>$v) {
            if (in_array($k, $arr) == false) {
                // 自定义注解类地址
                $file = ROOT_PATH.'/other/annotation/'.$k.'.php';
                // 存在则载入
                if (file_exists($file)) {
                    $class = '\other\annotation\\'.$k;
                    $obj = new $class;
                    $ret = $obj->run($v, 2);
                    if ($ret !== true) return $ret;
                }
            }
        }

        return true;
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
        $array = explode(\x\Config::get('route.suffix'), $request_uri);
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