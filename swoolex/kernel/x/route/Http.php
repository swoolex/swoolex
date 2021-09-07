<?php
/**
 * +----------------------------------------------------------------------
 * HTTP - 路由类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route;

use design\AbstractRoute;

class Http extends AbstractRoute {
    
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.18
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @return App
    */
    public function start(){
        // 获取容器
        $request = \x\context\Request::get();
        $request_uri = $this->format($request->server['request_uri']);
        // 先匹配出路由
        $route = \x\route\doc\Table::run()->get($request_uri, 'http');

        // 匹配不到
        if ($route == false) {
            $class = \x\Config::get('route.error_class');
            // 系统404
            if (\x\Config::get('route.404') == false || empty($class)) {
                // 实例化基类控制器
                $controller = new \x\controller\Http();
                $controller->fetch(\design\SystemTips::ROUTE_1, '404');
            } else {
            // 自定义404
                new $class();
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
    protected function ico_injection($route, $request_uri) {
        // 实例化控制器
        $reflection = new \ReflectionClass($route['n']);
        \x\context\Container::set('controller_instance', $reflection->newInstance());
        \x\context\Container::set('controller_method', $reflection->getmethod($route['name']));
        // 注册注解类

        // 达到峰值由生命周期抛出错误信息
        if (\x\Limit::routeVif($this->server, $this->fd, $request_uri, 'http') == false) return false;
        // 参数过滤
        $ret = (new \x\route\doc\lable\ParamHttp())->run($route);
        if ($ret !== true) return $ret;
        // Csrf
        $ret = (new \x\route\doc\lable\Csrf())->run($route);
        if ($ret !== true) return $ret;
        // Jwt
        $ret = (new \x\route\doc\lable\Jwt())->run($route);
        if ($ret !== true) return $ret;
        // 容器
        $ret = (new \x\route\doc\lable\Ioc())->run($route);
        if ($ret !== true) return $ret;
        // 单元测试操作
        $ret = (new \x\route\doc\lable\TestCase())->run($route, $request_uri);
        if ($ret !== true) return $ret;
        // 前置操作
        $ret = (new \x\route\doc\lable\AopBefore())->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround())->run($route);
        if ($ret !== true) return $ret;
        // 自定义注解
        $ret = $this->diy_annotation($route);
        if ($ret !== true) return $ret;
        // 异常操作 - 在这里触发控制器
        $ret = (new \x\route\doc\lable\AopThrows())->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround())->run($route, 2);
        if ($ret !== true) return $ret;
        // 后置操作
        $ret = (new \x\route\doc\lable\AopAfter())->run($route);
        if ($ret !== true) return $ret;

        return false;
    }

    
}