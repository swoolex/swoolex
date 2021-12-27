<?php
/**
 * +----------------------------------------------------------------------
 * 路由表
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;
use design\AbstractSingleCase;

class Route
{
    use AbstractSingleCase;
    
    /**
     * 路由表
    */
    private $list = [];
    /**
     * 临时的路由规则表
    */
    private $cache = [];
    /**
     * 统一前缀表
    */
    private $usual_prefix = [];
    /**
     * 通配符前缀表
    */
    private $wildcard_prefix = [];
    /**
     * 统一镜像表
    */
    private $usual_mirror = [];

    /**
     * 最终解析方法
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function register() {
        if (!$this->cache) return false;

        $data = [
            'n' => $this->cache['class'],
            'name' => $this->cache['action'],
            'method' => $this->cache['method'],
        ];
        if (isset($this->cache['param'])) {
            $data['own']['Param'] = $this->cache['param'];
        }
        if (isset($this->cache['limit'])) {
            $data['own']['Limit'] = $this->cache['limit'];
        }

        $route = $this->package($this->cache['route']);

        $this->list[$this->cache['server_type']][$route] = $data;
        
        $this->cache = [];

        return true;
    }

    /**
     * 设置路由统一镜像
     * @todo 无
     * @author 小黄牛
     * @version v2.5.18 + 2021-12-27
     * @deprecated 暂不启用
     * @global 无
     * @param array $rule 映射规则
     * @param string $server_type 服务类型 http/websocket/rpc
     * @return void
    */
    public function mirror($rule, $server_type='http') {
        if (!is_array($rule)) return false;
        $server_type = strtolower($server_type);

        foreach ($rule as $k => $v) {
            $original = $this->lrtrim($k);
            $current = $this->lrtrim($v);
            
            $this->usual_mirror[$server_type][$original] = $current;
        }
        return true;
    }
    
    /**
     * 读取镜像配置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.18 + 2021-12-27
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型 http/websocket/rpc
     * @return array
    */
    public function mirrorPack($server_type='http') {
        if (isset($this->usual_mirror[$server_type])) return $this->usual_mirror[$server_type];

        return [];
    }
    
    /**
     * 设置路由统一前缀
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param array $rule 映射规则
     * @param string $server_type 服务类型 http/websocket/rpc
     * @return void
    */
    public function prefix($rule, $server_type='http') {
        if (!is_array($rule)) return false;
        $server_type = strtolower($server_type);

        foreach ($rule as $prefix => $list) {
            $prefix = $this->lrtrim($prefix);
            foreach ($list as $v) {
                // 携带通配符
                if (substr($v, -1) == '*') {
                    $v = $this->lrtrim(rtrim($v, '*'));
                    // 先注册
                    $this->wildcard_prefix[$server_type][$v] = $prefix;
                    // 删除普通前缀
                    if (isset($this->usual_prefix[$server_type])) {
                        foreach ($this->usual_prefix[$server_type] as $key => $value) {
                            $num = stripos($key, $v);
                            if ($num === 0) {
                                unset($this->usual_prefix[$server_type][$key]);
                                continue;
                            }
                        }
                    }
                } else {
                    $v = $this->lrtrim($v);
                    // 先找通配符
                    $status = true;
                    if ($this->wildcard_prefix[$server_type]) {
                        foreach ($this->wildcard_prefix[$server_type] as $key => $value) {
                            $num = stripos($v, $key);
                            if ($num === 0) {
                                $status = false;
                                break;
                            }
                        }
                    }
                    if (!$status) continue;
                    // 最后注册
                    $this->usual_prefix[$server_type][$v] = $prefix;
                }
            }
        }

        return true;
    }
    /**
     * 路由规则分解
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @param string $method 路由允许的请求类型
     * @return void
    */
    public function resolve($server_type, $route, $class, $method=null) {
        $this->cache = [];
        
        if (strpos($class, '/')) {
            $class = str_replace('/', '\\', $this->lrtrim($class, '/'));
        } else {
            $class = $this->lrtrim($class, '\\');
        }

        $length = strrpos($class, '\\');
        $controller = substr($class, 0, $length);
        $action = substr($class, $length+1);

        switch ($server_type) {
            case 'http':
                if (\x\built\Str::iSstart($controller, 'app\\http') == false) {
                    $controller = 'app\\http\\'.$controller;
                }
            break;
            case 'websocket':
                if (\x\built\Str::iSstart($controller, 'app\\websocket') == false) {
                    $controller = 'app\\websocket\\'.$controller;
                }
            break;
            case 'rpc':
                if (\x\built\Str::iSstart($controller, 'app\\rpc') == false) {
                    $controller = 'app\\rpc\\'.$controller;
                }
            break;
        }

        $this->cache = [
            'server_type' => $server_type,
            'route' => $this->lrtrim($route),
            'class' => $controller,
            'action' => $action,
            'method' => $method,
        ];
        return $this;
    }
    /**
     * 设置HTTP路由
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @param string $method 路由允许的请求类型
     * @return void
    */
    public function http($route, $class=null, $method='GET|POST') {
        return $this->resolve('http', $route, $class, $method);
    }
    /**
     * 设置HTTP路由-GET
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @return void
    */
    public function get($route, $class=null) {
        return $this->http($route, $class, 'GET');
    }
    /**
     * 设置HTTP路由-POST
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @return void
    */
    public function post($route, $class=null) {
        return $this->http($route, $class, 'POST');
    }
    /**
     * 设置WebSocket路由
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @return void
    */
    public function websocket($route, $class=null) {
        return $this->resolve('websocket', $route, $class);
    }
    /**
     * 设置Rpc路由
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @param string $class 路由对应的命名空间地址
     * @return void
    */
    public function rpc($route, $class=null) {
        return $this->resolve('rpc', $route, $class);
    }
    /**
     * 设置@Param注解支持
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param array $data
     * @return void
    */
    public function param($data) {
        if (!$this->cache) return $this;
        foreach ($data as $key => $value) {
            $value['name'] = $key;
            $this->cache['param'][] = $value;
        }
        return $this;
    }
    /**
     * 设置@Limit注解支持 - 峰值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param int $peak 限流峰值
     * @return void
    */
    public function limitPeak($peak) {
        if (!$this->cache) return $this;

        $this->cache['limit']['peak'] = $peak;
        return $this;
    }
    /**
     * 设置@Limit注解支持 - 间隔时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param int $time 间隔时间
     * @return void
    */
    public function limitTime($time) {
        if (!$this->cache) return $this;
        
        $this->cache['limit']['time'] = $time;
        return $this;
    }
    /**
     * 设置@Limit注解支持 - 开始时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $date 开始时间
     * @return void
    */
    public function limitStart($time) {
        if (!$this->cache) return $this;
        
        $this->cache['limit']['start'] = $time;
        return $this;
    }
    /**
     * 设置@Limit注解支持 - 结束时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $date 结束时间
     * @return void
    */
    public function limitEnd($time) {
        if (!$this->cache) return $this;
        
        $this->cache['limit']['end'] = $time;
        return $this;
    }
    /**
     * 设置@Limit注解支持 - 回调生命周期
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 命名空间地址
     * @return void
    */
    public function limitCallback($callback) {
        if (!$this->cache) return $this;
        
        $this->cache['limit']['callback'] = $callback;
        return $this;
    }
    /**
     * 读取整张路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function readAll() {
        return $this->list;
    }
    /**
     * 根据前缀规则，读取出某条路由，拼接后的完整路由
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $route
     * @param string $server_type 服务类型 http/websocket/rpc
     * @return string
    */
    public function package($route, $server_type='http') {
        $cutting = \x\Config::get('route.cutting');
        $server_type = strtolower($server_type);

        // 先找普通的
        if (isset($this->usual_prefix[$server_type][$route])) {
            return $this->usual_prefix[$server_type][$route] . $cutting . $route;
        }
        // 再找通配符
        if (isset($this->wildcard_prefix[$server_type])) {
            foreach ($this->wildcard_prefix[$server_type] as $key => $value) {
                $num = stripos($route, $key);
                if ($num === 0) {
                    return $value . $cutting . $route;
                }
            }
        }

        return $route;
    }

    /**
     * 删除头尾路由分隔符
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $str 路由
     * @param string $rule 规则
     * @return void
    */
    private function lrtrim($str, $rule=null) {
        if ($str == '/') return $str;
        if (!$rule) $rule = \x\Config::get('route.cutting');

        return trim(ltrim(rtrim($str, $rule), $rule));
    }
}
