<?php
/**
 * +----------------------------------------------------------------------
 * Mqtt - 路由类
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
use design\SystemTips as Tips;
use x\middleware\Loader;

class Mqtt extends AbstractRoute {

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.2 + 2021-08-24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct($server, $fd, $obj, $function, $controller, $action) {
        $this->server = $server;
        $this->fd = $fd;
        $this->obj = $obj;
        $this->function = $function;
        $this->controller = $controller;
        $this->action = $action;
    }

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
        try {
            // 加入注解
            $request_uri = $this->controller.'/'.$this->action;
            $route = \x\route\doc\Table::run()->get($request_uri, 'mqtt');
            // 匹配不到
            if ($route == false) {
                // 调用服务
                return $this->function->invokeArgs($this->obj, []);
            } else { // 匹配到注解
                return $this->ico_injection($route, $request_uri);
            }
        } catch (\Throwable $throwable) {
            $msg = $throwable->getMessage().' Line：'.$throwable->getFile().'->'.$throwable->getLine();
            return false;
        }

        return false;
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
        \x\context\Container::set('controller_instance', $this->obj);
        \x\context\Container::set('controller_method', $this->function);
        // 注册注解类

        // 中间件 - 前置过滤
        $middleware_list = Loader::run()->hook($request_uri);
        if ($middleware_list) {
            $res = Loader::run()->handle($middleware_list, $this->server, $this->fd, 'mqtt');
            if (!$res) return false;
        }
        // 参数过滤
        $ret = (new \x\route\doc\lable\ParamMqtt($this->server, $this->fd))->run($route);
        if ($ret !== true) return $this->clean($ret);
        // 验证器
        $ret = (new \x\route\doc\lable\Validate($this->server, $this->fd))->run($route, 'mqtt');
        if ($ret !== true) return $ret;
        // 容器
        $ret = (new \x\route\doc\lable\Ioc($this->server, $this->fd))->run($route);
        if ($ret !== true) return $this->clean($ret);
        // 前置操作
        $ret = (new \x\route\doc\lable\AopBefore($this->server, $this->fd))->run($route);
        if ($ret !== true) return $this->clean($ret);
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($this->server, $this->fd))->run($route);
        if ($ret !== true) return $this->clean($ret);
        // 自定义注解
        $ret = $this->diy_annotation($this->server, $this->fd, $route);
        if ($ret !== true) return $this->clean($ret);
        // 异常操作 - 在这里触发控制器
        $return = (new \x\route\doc\lable\AopThrows($this->server, $this->fd))->run($route, true);
        if ($return !== true) return $this->clean($return);
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($this->server, $this->fd))->run($route, 2);
        if ($ret !== true) return $this->clean($ret);
        // 后置操作
        $ret = (new \x\route\doc\lable\AopAfter($this->server, $this->fd))->run($route);
        if ($ret !== true) return $this->clean($ret);
        // 中间件 - 后置通知
        if ($middleware_list) {
            $res = Loader::run()->end($middleware_list, $this->server, $this->fd, 'mqtt');
            if (!$res) return false;
        }
        return $return;
    }

    /**
     * 清除上下文
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021-08-24
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $ret
     * @return void
    */
    protected function clean($ret) {
        \x\context\Container::delete();
        return $ret;
    }
}