<?php
/**
 * +----------------------------------------------------------------------
 * Rpc - 路由类
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

class Rpc extends AbstractRoute {

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.2 + 2021-08-24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct($server, $fd, $obj, $function, $data) {
        $this->server = $server;
        $this->fd = $fd;
        $this->obj = $obj;
        $this->function = $function;
        $this->data = $data;
        $this->ServerCurrency = new \x\rpc\ServerCurrency();
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
            $request_uri = $this->data['class'].'/'.$this->data['function'];
            $route = \x\route\doc\Table::run()->get($request_uri, 'rpc');
            // 匹配不到
            if ($route == false) {
                // 调用服务
                $return = $this->function->invokeArgs($this->obj, []);
                return $this->prc_error($return);
            } else { // 匹配到注解
                return $this->ico_injection($route, $request_uri);
            }
        } catch (\Throwable $throwable) {
            $msg = $throwable->getMessage().' Line：'.$throwable->getFile().'->'.$throwable->getLine();
            $return = false;
            $this->create_rpc_error_log($this->data, $msg);
        }

        return $this->ServerCurrency->returnJson($this->server, $this->fd, '200', ((isset($this->obj->msg)) ? $this->obj->msg : 'SUCCESS'), $return);
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

        // 达到峰值由生命周期抛出错误信息
        if (\x\Limit::routeVif($this->server, $this->fd, $request_uri, 'rpc') == false) return false;
        // 参数过滤
        $ret = (new \x\route\doc\lable\ParamRpc($this->server, $this->fd))->run($route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '508', Tips::RPC_SERVER_ROUTE_8, $this->data);
        }
        // 验证器
        $ret = (new \x\route\doc\lable\Validate($this->server, $this->fd))->run($route, 'rpc');
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '508', Tips::RPC_SERVER_ROUTE_15, $this->data);
        }
        // 容器
        $ret = (new \x\route\doc\lable\Ioc($this->server, $this->fd))->run($route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '509', Tips::RPC_SERVER_ROUTE_9, $this->data);
        }
        // 前置操作
        $ret = (new \x\route\doc\lable\AopBefore($this->server, $this->fd))->run($route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '510', Tips::RPC_SERVER_ROUTE_10, $this->data);
        }
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($this->server, $this->fd))->run($route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '511', Tips::RPC_SERVER_ROUTE_11, $this->data);
        }
        // 自定义注解
        $ret = $this->diy_annotation($this->server, $this->fd, $route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '512', Tips::RPC_SERVER_ROUTE_12, $this->data);
        }
        // 异常操作 - 在这里触发控制器
        $return = (new \x\route\doc\lable\AopThrows($this->server, $this->fd))->run($route, true);
        // 环绕操作
        $ret = (new \x\route\doc\lable\AopAround($this->server, $this->fd))->run($route, 2);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '513', Tips::RPC_SERVER_ROUTE_13, $this->data);
        }
        // 后置操作
        $ret = (new \x\route\doc\lable\AopAfter($this->server, $this->fd))->run($route);
        if ($ret !== true) {
            return $this->ServerCurrency->returnJson($this->server, $this->fd, '514', Tips::RPC_SERVER_ROUTE_14, $this->data);
        }

        return $this->prc_error($return);
    }

    /**
     * 记录主动错误日志
     * @todo 无
     * @author 小黄牛
     * @version v2.5.2 + 2021-08-24
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $return
     * @return void
    */
    private function prc_error($return) {
        // 重新读取实例
        $obj = \x\context\Container::get('controller_instance');
        // 记录主动错误日志
        if (isset($obj->rpc_error) && $obj->rpc_error == true) {
            // 主动抛出错误日志内容
            if (isset($obj->rpc_msg)) {
                $return = $obj->rpc_msg;
            }
            // 重新读取请求参数注入
            $this->data['param'] = $obj->param;
            $this->create_rpc_error_log($this->data, $return);
        }

        return $this->ServerCurrency->returnJson($this->server, $this->fd, '200', ((isset($obj->msg)) ? $obj->msg : 'SUCCESS'), $return);
    }

    /**
     * 记录主动错误日志到Redis
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 请求节点信息
     * @param mixed $return 返回值
     * @return void
    */
    private function create_rpc_error_log($data, $return) {
        $max = \x\Config::get('rpc.rpc_error_max');

        $key = 'err_'.str_replace('/', '_', $this->data['class']).'|'.$data['function'];
        $redis = new \x\Redis();
        // 获取长度
        $res =  $redis->llen('rpc_err_list');
        if ($res == 0) {
            // 写入文件队列
            $redis->lpush('rpc_err_list', $key);
        }

        // 写入错误日志
        $ip = swoole_get_local_ip();
        $data['ip'] = current($ip);
        $data['port'] = \x\Config::get('server.port');
        $data['date'] = date('Y-m-d H:i:s', time());
        $length = $redis->lpush($key, json_encode(['config'=>$data, 'return' => $return], JSON_UNESCAPED_UNICODE));
        if ($length >= $max) {
            $redis->ltrim($key, 0, $max);
        }

        $redis->return();
    }
}