<?php
/**
 * +----------------------------------------------------------------------
 * 路由规则抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

abstract class AbstractRoute {
    
    /**
     * SessionID名
    */
    protected $session_name = 'PHPSESSID';

    /**
     * 服务实例
    */
    protected $server;

    /**
     * 客户端标识
    */
    protected $fd;

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @return void
    */
    public function __construct($server=null, $fd=null) {
        $this->server = $server;
        $this->fd = $fd;
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
    abstract public function start();

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
    abstract protected function ico_injection($route, $request_uri);

    /**
     * 自定义注解载入
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param array $route 被找到的路由
     * @return bool
    */
    protected final function diy_annotation($server, $fd, $route) {

        /**
         * 内置注解标签大全
        */
        $arr = [
            'Ioc', // 容器
            'RequestMapping', // 方法路由绑定
            'AopBefore', // 前置
            'AopAfter', // 后置
            'AopAround', // 环绕
            'AopThrows', // 异常
            'Controller', // 控制器路由绑定
            'onRoute', // 不允许访问的路由
            'Param', // 参数过滤
            'TestCase', // 单元测试
            'Csrf', // Csrf
            'Jwt', // Jwt
            'Limit', // Limit
        ];
        // 注册自定义注解类
        // 控制器注解
        if (isset($route['father'])) {
            foreach ($route['father'] as $k=>$v) {
                if (in_array($k, $arr) == false) {
                    // 自定义注解类地址
                    $file = ROOT_PATH.'/box/annotation/'.$k.'.php';
                    // 存在则载入
                    if (file_exists($file)) {
                        $class = '\box\annotation\\'.$k;
                        $obj = new $class($server, $fd);
                        $ret = $obj->run($v, 1);
                        if ($ret !== true) return $ret;
                    }
                }
            }
        }
        // 操作方法注解
        if (isset($route['own'])) {
            foreach ($route['own'] as $k=>$v) {
                if (in_array($k, $arr) == false) {
                    // 自定义注解类地址
                    $file = ROOT_PATH.'/box/annotation/'.$k.'.php';
                    // 存在则载入
                    if (file_exists($file)) {
                        $class = '\box\annotation\\'.$k;
                        $obj = new $class($server, $fd);
                        $ret = $obj->run($v, 2);
                        if ($ret !== true) return $ret;
                    }
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
    protected final function format($request_uri) {
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

    /**
     * Session注入
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected final function session() {
        // 获取容器
        $request = \x\context\Request::get();
        $response = \x\context\Response::get();

        if (!isset($request->cookie[$this->session_name])) {
            $config = \x\Config::get('app');
            $session_id = session_create_id();
            $request->cookie[$this->session_name] = $session_id;
            $response->cookie($this->session_name, $session_id, 0, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }

        // 更新容器
        \x\context\Request::set($request);
        \x\context\Response::set($response);
    }
}