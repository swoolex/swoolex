<?php
/**
 * +----------------------------------------------------------------------
 * 中间件加载
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\middleware;
use design\AbstractSingleCase;

class Loader
{
    use AbstractSingleCase;

    /**
     * 中间件配置
    */
    private $config = [];

    /**
     * 初始化中间件配置
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-18
     * @param array $route 路由
     * @return array
    */
    public function init() {
        if (!$this->config) {
            $this->config = \x\Config::get('middleware');
            foreach ($this->config as $key => $value) {
                $key = $this->lrtrim($key);
                $this->config[$key] = $value;
            }
        }
    }
    /**
     * 获取中间件
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-18
     * @param string $route 路由
     * @return array
    */
    public function hook($route) {
        $list = [];
        $route = $this->lrtrim($route);
        // 全局
        if (!empty($this->config['*'])) {
            $list = $this->config['*'];
        }
        // 单条
        if (!empty($this->config[$route])) {
            $arr = $this->config[$route];
            foreach ($arr as $value) {
                $list[] = $value;
            }
        }
        // 模糊匹配
        $arr = $this->resolve($route);
        foreach ($arr as $value) {
            $list[] = $value;
        }
        if (!$list) return [];

        // 去重
        return array_unique($list);
    }
    /**
     * 前置中间件加载
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-18
     * @param array $middleware_list 中间件列表
     * @param string $server 服务实例
     * @param mixed $fd 客户端标识符
     * @param string $service_type 服务类型
    */
    public function handle($middleware_list, $server, $fd, $service_type) {
        foreach ($middleware_list as $class) {
            $ref = new \ReflectionClass($class);
            $obj = $ref->newInstanceArgs([$server, $fd, $service_type]);
            if ($ref->hasMethod('handle')) {
                $method = $ref->getmethod('handle'); 
                $res = $method->invokeArgs($obj, []);
                if (!$res) return $res;
            }
        }
        return true;
    }
    /**
     * 后置中间件加载
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-18
     * @param array $middleware_list 中间件列表
     * @param string $server 服务实例
     * @param mixed $fd 客户端标识符
     * @param string $service_type 服务类型
    */
    public function end($middleware_list, $server, $fd, $service_type) {
        foreach ($middleware_list as $class) {
            $ref = new \ReflectionClass($class);
            $obj = $ref->newInstanceArgs([$server, $fd, $service_type]);
            if ($ref->hasMethod('end')) {
                $method = $ref->getmethod('end'); 
                $res = $method->invokeArgs($obj, []);
                if (!$res) return $res;
            }
        }
        return true;
    }
    /**
     * 路由分解
     * @author 小黄牛
     * @version v2.5.11 + 2021-11-18
     * @param array $route 路由
     * @return array
    */
    private function resolve($route) {
        $arr = explode('/', $route);
        $top_route = '';
        foreach ($arr as $v) {
            if ($top_route) {
                $top_route .= '/'.$v;
                $key = $top_route.'*';
                if (!empty($this->config[$key])) {
                    return $this->config[$key];
                }
            } else {
                $top_route = $v;
                $key = $top_route.'*';
                if (!empty($this->config[$key])) {
                    return $this->config[$key];
                }
                $key = $top_route.'/*';
                if (!empty($this->config[$key])) {
                    return $this->config[$key];
                }
            }
        }
        return [];
    }
    /**
     * 删除头尾路由分隔符
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @param string $str 路由
     * @param string $rule 规则
     * @return string
    */
    private function lrtrim($str) {
        if ($str == '/') return $str;
        $rule = '/';
        return trim(ltrim(rtrim($str, $rule), $rule));
    }
}