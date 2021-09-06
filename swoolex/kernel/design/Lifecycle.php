<?php
/**
 * +----------------------------------------------------------------------
 * 框架的生命周期回调 - 统一定义
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

class Lifecycle {

    /**
     * 当Worker进程Start完成时对Table的回调
     * @todo 只对WorkerID=0时回调
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function swoole_table_start($workerId) {
        // 只有第一个worker进程才能回调，不然会出现多次回调
        if ($workerId != 0) return false;
        
        // 防止reload时重复触发
        if (\x\Config::has('app.swoole_table_start')) return true;
        \x\Config::set('app.swoole_table_start', true);
        
        $list = \x\Config::get('swoole_table');
        foreach ($list as $v) {
            $obj = new \box\lifecycle\swoole_table_start();
            $obj->run($v['table'], $v['field'], $v['status']);
        }

        return true;
    }

    /**
     * 当Worker进程Start完成时的回调
     * @todo 只对WorkerID=0时回调
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function worker_start($workerId) {
        // 只有第一个worker进程才能回调，不然会出现多次回调
        if ($workerId != 0) return false;
        
        $obj = new \box\lifecycle\worker_start();
        $obj->run();
        return true;
    }

    /**
     * 当应用层捕捉到错误时，系统回调处理的生命周期
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param array $e 错误内容
     * @param string $error 系统自定义错误描述
     * @param array $source 错误上下文内容
     * @return void
    */
    public static function controller_error($e, $error, $source) {
        $obj = new \box\lifecycle\controller_error();
        $obj->run($e, $error, $source);
        unset($obj);
        return false;
    }
    
    /**
     * 推送失败的生命周期回调
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Server $server
     * @param json $content
     * @param int $fd
     * @return void
    */
    public static function websocket_push_error($server, $content, $fd) {
        $obj = new \box\lifecycle\websocket_push_error();
        $obj->run($server, $content, $fd);
        unset($obj);
        return false;
    }

    /**
     * 注解解析完成后的生命周期回调
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param array $route
     * @return void
    */
    public static function route_start($route) {
        $obj = new \box\lifecycle\route_start();
        $obj->run($route);
        unset($obj);
        return true;
    }

    /**
     * 当注解Param检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 回调事件
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return void
    */
    public static function annotate_param($callback, $tips, $name, $status, $attach=null) {
        $obj = new \box\lifecycle\annotate_param();
        $obj->run($tips, $name, $status, $attach);
        unset($obj);
        return true;
    }

    /**
     * 当检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function rpc_error($config, $status) {
        $obj = new \box\lifecycle\rpc_error();
        $obj->run($config['class'], $config['function'], $config, $status);
        return false;
    }

    /**
     * 当Mysql连接池数小于等于0时，回调的通知函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 连接池类型
     * @return void
    */
    public static function mysql_pop_error($type) {
        $obj = new \box\lifecycle\mysql_pop_error();
        $obj->run($type);
        return false;
    }

    /**
     * 单元测试注解的回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $tips 内容
     * @return void
    */
    public static function testcase_callback($tips) {
        $obj = new \box\lifecycle\testcase_callback();
        $obj->run($tips);
        return false;
    }

    /**
     * 当Redis连接池数小于等于0时，回调的通知函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 连接池类型
     * @return void
    */
    public static function redis_pop_error($type) {
        $obj = new \box\lifecycle\redis_pop_error();
        $obj->run($type);
        return false;
    }

    /**
     * 当其余注解检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.3 + 2021-08-25
     * @deprecated 暂不启用
     * @global 无
     * @param string $status 错误事件状态码
     * @return void
    */
    public static function route_error($status) {
        $obj = new \box\lifecycle\route_error();
        $obj->run($status);
        return false;
    }

    /**
     * JWT注解检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.3 + 2021-08-25
     * @deprecated 暂不启用
     * @global 无
     * @param string $status 错误事件状态码
     * @return void
    */
    public static function jwt_error($status) {
        $obj = new \box\lifecycle\jwt_error();
        $obj->run($status);
        return false;
    }

    /**
     * CSRF注解检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.3 + 2021-08-25
     * @deprecated 暂不启用
     * @global 无
     * @param string $status 错误事件状态码
     * @return void
    */
    public static function csrf_error($status) {
        $obj = new \box\lifecycle\csrf_error();
        $obj->run($status);
        return false;
    }

    /**
     * 路由限流器达到峰值时，回调的通知函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.3 + 2021-08-25
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 回调地址
     * @param string $server_type 服务类型 http/websocket/rpc
     * @param string $route 触发路由
     * @param string $data 对应限流配置信息
     * @return void
    */
    public static function limit_route($callback, $server_type, $route, $data) {
        $obj = new $callback;
        $obj->run($server_type, $route, $data);
        return false;
    }

    /**
     * IP限流器达到峰值时，回调的通知函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.3 + 2021-08-25
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 回调地址
     * @param string $server_type 服务类型 http/websocket/rpc/mqtt
     * @param string $ip 触发IP
     * @param string $data 对应限流配置信息
     * @return void
    */
    public static function limit_ip($callback, $server_type, $ip, $data) {
        $obj = new $callback;
        $obj->run($server_type, $ip, $data);
        return false;
    }
}