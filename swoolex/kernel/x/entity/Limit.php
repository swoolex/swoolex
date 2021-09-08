<?php
/**
 * +----------------------------------------------------------------------
 * 限流器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\entity;

class Limit
{
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}
    /**
     * 路由限流表
    */
    private $route = [];
    /**
     * 路由定时器队列
    */
    private $route_time = [];
    /**
     * IP限流表
    */
    private $ip = [];
    /**
     * IP定时器队列
    */
    private $ip_time = [];
    /**
     * 路由临时缓存表
    */
    private $cache_route = [];
    /**
     * IP临时缓存表
    */
    private $cache_ip = [];
    /**
     * 自增器前缀
    */
    private $prefix = 'swx_';
    /**
     * 路由器配置
    */
    private $config = [];
    
    /**
     * 单例入口
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        self::$instance->config = \x\Config::get('limit');
        return self::$instance;
    }

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
        if ($this->cache_route) {
            if ($this->config['route']['limit_switch'] == false) return false;

            foreach ($this->cache_route as $key => $value) {
                if (isset($this->route[$value['server_type']][$key])) {
                    $time = $this->route[$value['server_type']][$key]['time'];
                    unset($this->route_time[$value['server_type']][$time][$key]);
                }
                if (!isset($value['status'])) $value['status'] = true;
                if (empty($value['time'])) $value['time'] = $this->config['route']['reset_time'];
                $this->route[$value['server_type']][$key] = $value;
                $this->route_time[$value['server_type']][$value['time']][$key] = 1;
                
                $redis_key = $this->prefix . $value['server_type'] . $key;
                \x\swoole\Atomic::create($redis_key, 1);
            }
            $this->cache_route = [];
        } else if ($this->cache_ip) {
            if ($this->config['ip']['limit_switch'] == false) return false;

            foreach ($this->cache_ip as $key => $value) {
                if (isset($this->ip[$key])) {
                    $time = $this->ip[$key]['time'];
                    unset($this->ip_time[$time][$key]);
                }
                if (!isset($value['status'])) $value['status'] = true;
                $this->ip[$key] = $value;
                if (empty($value['time'])) $value['time'] = $this->config['ip']['reset_time'];
                $this->ip_time[$value['time']][$key] = 1;

                $redis_key = $this->prefix . $key;
                \x\swoole\Atomic::create($redis_key, 1);
            }
            $this->cache_ip = [];
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
     * @param array $config 配置
     * @return void
    */
    public function resolve($server_type, $route, $config=[]) {
        $this->cache_route = [];

        $list = [];
        if (is_array($route)) {
            foreach ($route as $key => $value) {
                if (is_array($value)) {
                    $list[$key] = $value;
                } else {
                    $list[$value] = $config;
                }
            }
        } else {
            $list[$route] = $config;
        }

        foreach ($list as $key => $value) {
            $route = \x\Route::package($this->lrtrim($key), $server_type);
            $value['server_type'] = $server_type;
            $this->cache_route[$route] = $value;
        }
        
        return $this;
    }
    /**
     * 设置HTTP路由限流
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @return void
    */
    public function http($route) {
        return $this->resolve('http', $route);
    }
    /**
     * 设置WebSocket路由限流
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @return void
    */
    public function webscoekt($route) {
        return $this->resolve('websocket', $route);
    }
    /**
     * 设置RPC路由限流
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $route 路由地址
     * @return void
    */
    public function rpc($route) {
        return $this->resolve('rpc', $route);
    }
    /**
     * 提供给服务注册全局路由限流器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string|array $route 路由地址
     * @param array $config 配置
     * @param bool $doc 是否注解注册
     * @return void
    */
    public function routeSet($server_type, $route, $config=[], $doc=false) {
        if ($this->config['route']['global_switch'] == false && $doc == false) return $this;
        if ($this->routeHas($server_type, $route)) return $this;

        if (isset($config['status']) && strlen($config['status']) > 1) {
            if (strtolower($config['status']) == 'false') {
                $config['status'] = false;
            } else {
                $config['status'] = true;
            }
        }
        return $this->resolve($server_type, $route, $config);
    }
    /**
     * 提供给服务注册全局IP限流器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip IP地址
     * @param array $config 配置
     * @return void
    */
    public function ipSet($ip, $config=[]) {
        if ($this->config['ip']['limit_switch'] == false) return $this;
        if ($this->ipHas($ip)) return $this;

        return $this->ip($ip, $config);
    }
    /**
     * 设置IP限流
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $ip IP地址
     * @param array $config 配置
     * @return void
    */
    public function ip($ip, $config=[]) {
        $this->cache_ip = [];

        $list = [];
        if (is_array($ip)) {
            foreach ($ip as $key => $value) {
                if (is_array($value)) {
                    $list[$key] = $value;
                } else {
                    $list[$value] = $config;
                }
            }
        } else {
            $list[$ip] = $config;
        }

        foreach ($list as $key => $value) {
            $this->cache_ip[$key] = $value;
        }
        
        return $this;
    }
    /**
     * 设置限流峰值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param int $peak 限流峰值
     * @return void
    */
    public function peak($peak) {
        if ($this->cache_route) {
            foreach ($this->cache_route as $key => $value) {
                $this->cache_route[$key]['peak'] = $peak;
            }
            return $this;
        } else if ($this->cache_ip) {
            foreach ($this->cache_ip as $key => $value) {
                $this->cache_ip[$key]['peak'] = $peak;
            }
            return $this;
        } 
        
        return false;
    }
    /**
     * 设置限流间隔时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param int $time 间隔时间
     * @return void
    */
    public function time($time) {
        if ($this->cache_route) {
            foreach ($this->cache_route as $key => $value) {
                $this->cache_route[$key]['time'] = $time;
            }
            return $this;
        } else if ($this->cache_ip) {
            foreach ($this->cache_ip as $key => $value) {
                $this->cache_ip[$key]['time'] = $time;
            }
            return $this;
        } 
        
        return false;
    }
    /**
     * 设置限流开始时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $date 开始时间
     * @return void
    */
    public function start($date) {
        if ($this->cache_route) {
            foreach ($this->cache_route as $key => $value) {
                $this->cache_route[$key]['start'] = $date;
            }
            return $this;
        } else if ($this->cache_ip) {
            foreach ($this->cache_ip as $key => $value) {
                $this->cache_ip[$key]['start'] = $date;
            }
            return $this;
        } 
        
        return false;
    }
    /**
     * 设置限流结束时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $date 结束时间
     * @return void
    */
    public function end($date) {
        if ($this->cache_route) {
            foreach ($this->cache_route as $key => $value) {
                $this->cache_route[$key]['end'] = $date;
            }
            return $this;
        } else if ($this->cache_ip) {
            foreach ($this->cache_ip as $key => $value) {
                $this->cache_ip[$key]['end'] = $date;
            }
            return $this;
        } 
        
        return false;
    }
    /**
     * 设置限流峰值生命周期回调地址
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 命名空间地址
     * @return void
    */
    public function callback($callback) {
        if ($this->cache_route) {
            foreach ($this->cache_route as $key => $value) {
                $this->cache_route[$key]['callback'] = $callback;
            }
            return $this;
        } else if ($this->cache_ip) {
            foreach ($this->cache_ip as $key => $value) {
                $this->cache_ip[$key]['callback'] = $callback;
            }
            return $this;
        } 
        
        return false;
    }
    /**
     * 读取路由表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string $route 路由地址
     * @return void
    */
    public function readRouteAll($server_type=null, $route=null) {
        if ($this->config['route']['limit_switch'] == false) return [];
        if (is_null($server_type)) return $this->route;
        if (is_null($route)) return $this->route[$server_type] ?? [];
        return $this->route[$server_type][$route] ?? [];
    }
    /**
     * 读取整张IP表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip ip地址
     * @return void
    */
    public function readIpAll($ip=null) {
        if ($this->config['ip']['limit_switch'] == false) return [];
        if (is_null($ip)) return $this->ip;
        return $this->ip[$ip] ?? [];
    }
    /**
     * 读取路由时间表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param int $time 秒数
     * @return void
    */
    public function readRouteTimeAll($server_type=null, $time=null) {
        if ($this->config['route']['limit_switch'] == false) return [];
        if (is_null($server_type)) return $this->route_time;
        if (is_null($time)) return $this->route_time[$server_type] ?? [];
        return $this->route_time[$server_type][$time] ?? [];
    }
    /**
     * 读取整张IP时间表
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param int $time 秒数
     * @return void
    */
    public function readIpTimeAll($time=null) {
        if ($this->config['ip']['limit_switch'] == false) return [];
        if (is_null($time)) return $this->ip_time;
        return $this->ip_time[$time] ?? [];
    }
    /**
     * 查看某条路由是否在限流器中
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string $route 路由地址
     * @return bool
    */
    public function routeHas($server_type, $route) {
        return isset($this->route[$server_type][$route]);
    }
    /**
     * 查看某条IP是否在限流器中
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip IP地址
     * @return bool
    */
    public function ipHas($ip) {
        return isset($this->ip[$ip]);
    }
    /**
     * 触发一个路由自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string $route 路由地址
     * @return bool
    */
    public function routeAtomicInc($server_type, $route) {
        $key = $this->prefix . $server_type . $route;
        if (!\x\swoole\Atomic::has($key)) return false;
            
        return \x\swoole\Atomic::setInc($key, 1);
    }
    /**
     * 触发一个IP自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip IP地址
     * @return bool
    */
    public function ipAtomicInc($ip) {
        $key = $this->prefix . $ip;
        if (!\x\swoole\Atomic::has($key)) return false;
        
        return \x\swoole\Atomic::setInc($key, 1);
    }
    /**
     * 重置一个路由自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string $route 路由地址
     * @return bool
    */
    public function routeAtomicReset($server_type, $route) {
        if (!$this->routeHas($server_type, $route)) return false;

        $key = $this->prefix . $server_type . $route;
        if (!\x\swoole\Atomic::has($key)) return false;
        
        return \x\swoole\Atomic::set($key, 1);
    }
    /**
     * 重置一个IP自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip IP地址
     * @return bool
    */
    public function ipAtomicReset($ip) {
        if (!$this->ipHas($ip)) return false;

        $key = $this->prefix . $ip;
        if (!\x\swoole\Atomic::has($key)) return false;
            
        return \x\swoole\Atomic::set($key, 1);
    }
    /**
     * 获取一个路由自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type 服务类型
     * @param string $route 路由地址
     * @return void
    */
    public function routeAtomicGet($server_type, $route) {
        $key = $this->prefix . $server_type . $route;
        if (!\x\swoole\Atomic::has($key)) return 0;

        return \x\swoole\Atomic::get($key);
    }
    /**
     * 获取一个IP自增器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param string $ip IP地址
     * @return bool
    */
    public function ipAtomicGet($ip) {
        $key = $this->prefix . $ip;
        if (!\x\swoole\Atomic::has($key)) return 0;

        return \x\swoole\Atomic::get($key);
    }
    /**
     * 判断一个路由限流器是否超值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param string $route 路由地址
     * @param string $server_type 服务类型
     * @return bool
    */
    public function routeVif($server, $fd, $route, $server_type) {
        if ($this->config['route']['limit_switch'] == false) return true;
        if (!$this->routeHas($server_type, $route)) return true;

        $data = $this->route[$server_type][$route];
        if (isset($data['status']) && $data['status'] == false) return true;
        if (!isset($data['start'])) $data['start'] = $this->config['route']['start_date'];
        if (!isset($data['end'])) $data['end'] = $this->config['route']['end_date'];
        if (!isset($data['peak'])) $data['peak'] = $this->config['route']['peak_num'];
        if (!isset($data['callback'])) $data['callback'] = $this->config['route']['callback'];

        $time = time();
        // 日期判断
        if ($data['start']) {
            // 还没开始
            if (\x\built\Time::dateTurnTime($data['start']) > $time) return true;
        }
        if ($data['end']) {
            // 已经结束
            if (\x\built\Time::dateTurnTime($data['end']) < $time) return true;
        }

        // 读取
        if ($this->routeAtomicGet($server_type, $route) > $data['peak']) {
            // 已经达到峰值
            \design\Lifecycle::limit_route($server, $fd, $data['callback'], $server_type, $route, $data);
            return false;
        }

        // 继续自增
        return $this->routeAtomicInc($server_type, $route);
    }
    /**
     * 判断一个IP限流器是否超值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server 服务实例
     * @param string $fd 客户端标识
     * @param string $ip IP地址
     * @param string $server_type 服务类型
     * @return bool
    */
    public function ipVif($server, $fd, $ip, $server_type) {
        if ($this->config['ip']['limit_switch'] == false) return true;
        if (!$this->ipHas($ip)) return true;

        $data = $this->ip[$ip];
        if (isset($data['status']) && $data['status'] == false) return true;
        if (!isset($data['start'])) $data['start'] = $this->config['ip']['start_date'];
        if (!isset($data['end'])) $data['end'] = $this->config['ip']['end_date'];
        if (!isset($data['peak'])) $data['peak'] = $this->config['ip']['peak_num'];
        if (!isset($data['callback'])) $data['callback'] = $this->config['ip']['callback'];

        $time = time();
        // 日期判断
        if ($data['start']) {
            // 还没开始
            if (\x\built\Time::dateTurnTime($data['start']) > $time) return true;
        }
        if ($data['end']) {
            // 已经结束
            if (\x\built\Time::dateTurnTime($data['end']) < $time) return true;
        }
        // 读取
        if ($this->ipAtomicGet($ip) > $data['peak']) {
            // 已经达到峰值
            \design\Lifecycle::limit_ip($server, $fd, $data['callback'], $server_type, $ip, $data);
            return false;
        }

        // 继续自增
        return $this->ipAtomicInc($ip);
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
        if (!$rule) $rule = \x\Config::get('route.cutting');;

        return trim(ltrim(rtrim($str, $rule), $rule));
    }
}