<?php
// +----------------------------------------------------------------------
// | 微服务-服务端路由转发
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed (http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\rpc;

class ServerRoute
{
    /**
     * 微服务请求路由转发
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param server $server
     * @param fd $fd
     * @param reactorId $reactorId
     * @param array $data
     * @return void
    */
    public function start($server, $fd, $reactorId, $data) {
        $ServerCurrency = new ServerCurrency();
        if (!$data) return $ServerCurrency->returnJson($server, $fd, '501', 'The data is empty, maybe AES decryption failed！');
        if (empty($data['class'])) return $ServerCurrency->returnJson($server, $fd, '502', 'Parameter class cannot be empty！');
        if (empty($data['function'])) return $ServerCurrency->returnJson($server, $fd, '503', 'Parameter function cannot be empty！');

        $class = '\app\rpc\\'.str_replace('/', '\\', ltrim(rtrim($data['class'], '/'), '/'));
        if (!class_exists($class)) return $ServerCurrency->returnJson($server, $fd, '504', 'The requested processing class does not exist！');
        $ref = new \ReflectionClass($class);
        if (!$ref->hasMethod($data['function'])) return $ServerCurrency->returnJson($server, $fd, '505', 'The requested method does not exist！');

        // 实例化操作方法
        $function = $ref->getmethod($data['function']);
        if ($function->isStatic()) return $ServerCurrency->returnJson($server, $fd, '506', 'Static classes cannot be called！');
        if (!$function->isPublic()) return $ServerCurrency->returnJson($server, $fd, '507', 'Private or protected methods cannot be called！');
        
        // 成员属性注入
        $obj = $ref->newInstance();
        $obj->headers = $data['headers'] ?? [];
        $obj->param = $data['param'] ?? [];
        // 调用服务
        $return = $function->invokeArgs($obj, []);
        $return = $return ? $return : [];
        return $ServerCurrency->returnJson($server, $fd, '200', 'SUCCESS', $return);
    }
}