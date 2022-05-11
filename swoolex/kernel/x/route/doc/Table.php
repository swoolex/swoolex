<?php
/**
 * +----------------------------------------------------------------------
 * 注解表
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route\doc;

class Table {
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}
    /**
     * 路由表，由内存存储
    */
    private $table = [];
    /**
     * 镜像路由配置
    */
    private $mirror = [];
    /**
     * 默认方法
    */
    private $default_action;

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @return Table
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new Table();
            self::$instance->default_action = \x\Config::get('route.default_action');
        }
        return self::$instance;
    }

    /**
     * HTTP-初始化路由表
     * @author 小黄牛
     * @version v1.1.6 + 2020.07.15
    */
    public function start_http() {
        $cutting = \x\Config::get('route.cutting');

        // http路由
        $type = 'http';
        $this->table = \x\Route::readAll();
        $this->mirror = \x\Route::mirrorPack($type);
        $list = $this->every_file(ROOT_PATH.'app'.DS.$type);
        $this->add_list($list, $cutting, $type);
    }

    /**
     * WebSocket-初始化路由表
     * @author 小黄牛
     * @version v1.1.6 + 2020.07.15
    */
    public function start_websocket() {
        $cutting = \x\Config::get('route.cutting');

        // websocket路由
        $list = $this->every_file(ROOT_PATH.'app'.DS.'websocket');
        $this->add_list($list, $cutting, 'websocket');
    }

    /**
     * RPC-初始化路由表
     * @author 小黄牛
     * @version v2.5.2 + 2021-08-24
    */
    public function start_rpc() {
        $cutting = \x\Config::get('route.cutting');
        
        // rpc路由
        $type = 'rpc';
        $this->table = \x\Route::readAll();
        $this->mirror = \x\Route::mirrorPack($type);
        $list = $this->every_file(ROOT_PATH.'app'.DS.$type);
        $this->add_list($list, $cutting, $type);
    }

    /**
     * MQTT-初始化路由表
     * @author 小黄牛
     * @version v2.5.2 + 2021-08-24
    */
    public function start_mqtt() {
        $cutting = \x\Config::get('route.cutting');
        
        // mqtt路由
        $type = 'mqtt';
        $this->table = \x\Route::readAll();
        $this->mirror = \x\Route::mirrorPack($type);
        $list = $this->every_file(ROOT_PATH.'app'.DS.$type);
        $this->add_list($list, $cutting, $type);
    }

    /**
     * 更新缓存
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
    */
    public function start_cache() {
        // 新增生命周期回调事件
        $route = $this->route();
        \design\Lifecycle::route_start($route);

        // 写入路由缓存
        $route_file = \x\Config::get('server.route_file');
        $json = json_encode($route, JSON_UNESCAPED_UNICODE);
        file_put_contents($route_file, $json);
    }

    /**
     * 查询路由
     * @author 小黄牛
     * @version v1.0.2 + 2020.06.12
     * @param string $url
     * @param string $route_type 路由类型
     * @return array|false
    */
    public function get($url, $route_type) {
        if ($url != '/') $url = rtrim($url, '/');
        
        if (isset($this->table[$route_type][$url])) {
            return $this->table[$route_type][$url];
        }

        return false;
    }

    /**
     * 获取整张路由表
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return array
    */
    public function route() {
        return $this->table;
    }

    /**
     * 注解的路由注入路由表
     * @author 小黄牛
     * @version v1.0.2 + 2020.06.12
     * @param array $doc 注解
     * @param string $namespace 命名空间
     * @param string $cutting 路由分隔符
     * @param string $route_type 路由类型
     * @return bool
    */
    private function add_doc_route($doc, $namespace, $cutting, $route_type) {
        if ($doc == false) return false;

        $class = $doc['class'];
        $function = $doc['function'];

        // 路由前缀
        $prefix = '';
        if (isset($class['Controller']['prefix'])) {
            $prefix = ltrim($class['Controller']['prefix'], '/');
            // 删掉，不然浪费内存
            unset($class['Controller']['prefix']);
        }

        foreach ($function as $name => $val) {
            # 判断是否不允许访问的路由
            if (isset($val['onRoute'])) {
                continue;
            }
            
            $array = [
                'n' => $namespace,
                'name' => $name,
            ];

            if (isset($val['RequestMapping']['route'])) {
                $url = $prefix.$val['RequestMapping']['route'];
                if ($url != '/') {
                    $url = ltrim($url, $cutting);
                }
                if (isset($val['RequestMapping']['method'])) {
                    $array['method'] = $val['RequestMapping']['method'];
                    unset($val['RequestMapping']['method']);
                }
                if (isset($val['RequestMapping']['title'])) {
                    $array['title'] = $val['RequestMapping']['title'];
                    unset($val['RequestMapping']['title']);
                }
                unset($val['RequestMapping']['route']);
            } else {
                $url = str_replace(['app\\'.$route_type.'\\', '\\'], ['', $cutting], $namespace);
                if ($name != $this->default_action) {
                    $url .= $cutting.$name;
                }
            }

            $array['father'] = $class;
            $array['own'] = $val;

            $url = \x\Route::package(strtolower($url), $route_type);
            if (isset($this->table[$route_type][$url])) {
                $param = $this->table[$route_type][$url];
                $array['n'] = $param['n'];
                $array['name'] = $param['name'];
                // HTTP路由独有参数
                if (isset($param['method'])) $array['method'] = $param['method'];
                // 限流注解
                if (!empty($array['own']['Limit']) && !empty($param['own']['Limit'])) {
                    if (empty($array['own']['Limit']['peak']) && !empty($param['own']['peak'])) $array['own']['Limit']['peak'] = $param['own']['Limit']['peak'];
                    if (empty($array['own']['Limit']['time']) && !empty($param['own']['time'])) $array['own']['Limit']['time'] = $param['own']['Limit']['time'];
                    if (empty($array['own']['Limit']['start']) && !empty($param['own']['start'])) $array['own']['Limit']['start'] = $param['own']['Limit']['start'];
                    if (empty($array['own']['Limit']['end']) && !empty($param['own']['end'])) $array['own']['Limit']['end'] = $param['own']['Limit']['end'];
                } else if (!empty($param['own']['Limit'])){
                    $array['own']['Limit'] = $param['own']['Limit'];
                }
                // 参数注解
                if (!empty($array['own']['Param']) && !empty($param['own']['Param'])) {
                    foreach ($param['own']['Param'] as $value) {
                        $array['own']['Param'][] = $value;
                    }
                } else if (!empty($param['own']['Param'])){
                    $array['own']['Param'] = $param['own']['Param'];
                }
            }
            foreach ($this->mirror as $original => $current) {
                if (stripos($url, $original) === 0) {
                    $url = substr_replace($url, $current,strpos($url, $original), strlen($original));
                    break;
                }
            }
            $this->table[$route_type][$url] = $array;

            // 限流器注册
            if (!empty($array['own']['Limit'])) {
                \x\Limit::routeSet($route_type, $url, $array['own']['Limit'], true)->register();
            } else {
                \x\Limit::routeSet($route_type, $url)->register();
            }
        }
    }

    /**
     * 遍历目录下的所有文件
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @param string $dir 地址
     * @return array
    */
    private function every_file($dir, $list=[]) {
        $handle = opendir($dir);
        while ($line = readdir($handle)) {
            if ($line != '.' && $line != '..') {
                if (is_dir($dir .'/'. $line)) {
                    $list = $this->every_file($dir .'/'. $line, $list);
                } else {
                    $list[] = $dir.'/'.$line;
                }
            }
        }
        // 关闭目录
        closedir($handle);
        return $list;
    }

    /**
     * 遍历指定目录下的注解
     * @author 小黄牛
     * @version v1.0.2 + 2020.06.12
     * @param array $list 目录结构
     * @param array $$cutting 路由配置
     * @param string $route_type 路由类型
    */
    private function add_list($list, $cutting, $route_type) {
        foreach ($list as $path) {
            $fp = fopen($path, "r");
            $size = filesize($path);
            if ($size > 0) {
                $str = fread($fp, filesize($path));
                if (preg_match('/namespace(.*);/i', $str, $comment ) === false) continue;
                if (!isset($comment[1])) continue;
    
                $suffix = substr(strrchr($path, '.'), 1);
                $result = basename($path,".".$suffix);
    
                # 获得命名空间地址
                $namespace = trim($comment[1]).'\\'.$result;
                
                # 使用注解
                $doc = \x\route\doc\Annotate::run($namespace);
                $this->add_doc_route($doc, $namespace, $cutting, $route_type);
            }
        }
    }
}