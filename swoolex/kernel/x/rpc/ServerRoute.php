<?php
/**
 * +----------------------------------------------------------------------
 * 微服务-服务端路由转发
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\rpc;
use design\SystemTips as Tips;

class ServerRoute
{
    /**
     * 微服务请求路由转发
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @param server $server
     * @param fd $fd
     * @param reactorId $reactorId
     * @param array $data
     * @return object
    */
    public function start($server, $fd, $reactorId, $data) {
        $ServerCurrency = new ServerCurrency();
        if (!$data) return $ServerCurrency->returnJson($server, $fd, '501', Tips::RPC_SERVER_ROUTE_1, $data);
        if (empty($data['class'])) return $ServerCurrency->returnJson($server, $fd, '502', Tips::RPC_SERVER_ROUTE_2, $data);
        if (empty($data['function'])) return $ServerCurrency->returnJson($server, $fd, '503', Tips::RPC_SERVER_ROUTE_3, $data);

        $class = '\app\rpc\\'.str_replace('/', '\\', ltrim(rtrim($data['class'], '/'), '/'));
        if (!class_exists($class)) return $ServerCurrency->returnJson($server, $fd, '504', Tips::RPC_SERVER_ROUTE_4, $data);

        $res = \x\Rpc::limit($data, $fd);
        if (!$res) return $ServerCurrency->returnJson($server, $fd, '516', Tips::RPC_SERVER_ROUTE_16, $data);

        $ref = new \ReflectionClass($class);
        if (!$ref->hasMethod($data['function'])) return $ServerCurrency->returnJson($server, $fd, '505', Tips::RPC_SERVER_ROUTE_5, $data);

        // 实例化操作方法
        $function = $ref->getmethod($data['function']);
        if ($function->isStatic()) return $ServerCurrency->returnJson($server, $fd, '506', Tips::RPC_SERVER_ROUTE_6, $data);
        if (!$function->isPublic()) return $ServerCurrency->returnJson($server, $fd, '507', Tips::RPC_SERVER_ROUTE_7, $data);

        // 成员属性注入
        $obj = $ref->newInstance();
        $obj->headers = $data['headers'] ?? [];
        $obj->param = $data['param'] ?? [];
        
        \x\context\Container::set('controller_reflection', $ref);
        return (new \x\route\Rpc($server, $fd, $obj, $function, $data))->start();
    }

}