<?php
/**
 * +----------------------------------------------------------------------
 * WebSocket - 路由类
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
use x\middleware\Loader;

class WebSocket extends AbstractRoute {
    
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
        $obj = new \x\controller\WebSocket();
        $data = $obj->get_data();
        $request_uri = $data['action'];
        // 先匹配出路由
        $route = \x\route\doc\Table::run()->get($request_uri, 'websocket');

        // 匹配不到
        if ($route == false) {
            $obj->fetch('error', '500', $request_uri.' 路由不存在~~');
        } else {
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
        \x\context\Container::set('controller_reflection', $reflection);
        \x\context\Container::set('controller_instance', $reflection->newInstance());
        \x\context\Container::set('controller_method', $reflection->getmethod($route['name']));
        // 注册注解类

        // 达到峰值由生命周期抛出错误信息
        $server = \x\context\Container::get('websocket_server');
        $frame = \x\context\Container::get('websocket_frame');
        if (\x\Limit::routeVif($server, $frame->fd, $request_uri, 'websocket') == false) return false;
        // 中间件 - 前置过滤
        $middleware_list = Loader::run()->hook($request_uri);
        if ($middleware_list) {
            $res = Loader::run()->handle($middleware_list, $server, $frame->fd, 'websocket');
            if (!$res) return false;
        }
        // 参数过滤
        $ret = (new \x\route\doc\lable\ParamWebSocket($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 验证器
        $ret = (new \x\route\doc\lable\Validate($server, $frame->fd))->run($route, 'websocket');
        if ($ret !== true) return $ret;
        // 容器
        $ret = (new \x\route\doc\lable\Ioc($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 单元测试操作
        $ret = (new \x\route\doc\lable\TestCase($server, $frame->fd))->run($route, $request_uri);
        if ($ret !== true) return $ret;
        // 前置操作
        $ret = (new \x\route\doc\lable\AopBefore($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 自定义注解
        $ret = $this->diy_annotation($server, $frame->fd, $route);
        if ($ret !== true) return $ret;
        // 异常操作 - 在这里触发控制器
        $ret = (new \x\route\doc\lable\AopThrows($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($server, $frame->fd))->run($route, 2);
        if ($ret !== true) return $ret;
        // 后置操作
        $ret = (new \x\route\doc\lable\AopAfter($server, $frame->fd))->run($route);
        if ($ret !== true) return $ret;
        // 中间件 - 后置通知
        if ($middleware_list) {
            $res = Loader::run()->end($middleware_list, $server, $frame->fd, 'websocket');
            if (!$res) return false;
        }
        return false;
    }
}