<?php
// +----------------------------------------------------------------------
// | 路由表模式 - 单例
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\route;

class Table
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 路由表，由内存存储
    */
    private $table = [];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @return Table
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new Table();
        }
        return self::$instance;
    }

    /**
     * 初始化路由表
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function start() {
        $cutting = \x\Config::run()->get('route.cutting');
        $list = $this->every_file(ROOT_PATH.'/app/controller');

        \x\StartEo::run(\x\Lang::run()->get('start -6'));

        foreach ($list as $path) {
            $fp = fopen($path, "r");
            $str = fread($fp, filesize($path));

            if (preg_match('/namespace(.*);/i', $str, $comment ) === false) continue;
            if (!isset($comment[1])) continue;

            $suffix = substr(strrchr($path, '.'), 1);
            $result = basename($path,".".$suffix);

            # 获得命名空间地址
            $namespace = trim($comment[1]).'\\'.$result;
            
            # 使用注解
            $doc = \x\doc\Annotate::run($namespace);
            $this->add_doc_route($doc, $namespace, $cutting);
        }
        \x\StartEo::run(\x\Lang::run()->get('start -7'));
        \x\StartEo::run('Route：total '.count($this->table));
    }

    /**
     * 查询路由
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $url
     * @return array|false
    */
    public function get($url) {
        if (isset($this->table[$url])) {
            return $this->table[$url];
        }

        return false;
    }

    /**
     * 获取整张路由表
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public function route() {
        return $this->table;
    }

    /**
     * 注解的路由注入路由表
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param array $doc 注解
     * @param string $namespace 命名空间
     * @param string $cutting 路由分隔符
     * @return void
    */
    private function add_doc_route($doc, $namespace, $cutting) {
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
                $url = str_replace(['app\controller\\', '\\'], ['', $cutting], $namespace).$cutting.$name;
            }

            $array['father'] = $class;
            $array['own'] = $val;

            $this->table[$url] = $array;
        }
    }

    /**
     * 遍历目录下的所有文件
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $dir 地址
     * @return void
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
}