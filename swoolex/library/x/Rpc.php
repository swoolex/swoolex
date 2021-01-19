<?php
// +----------------------------------------------------------------------
// | 微服务-配置调用类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Rpc
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 全站配置项
    */
    private static $config = [];

    /**
     * 实例化对象方法，供外部获得唯一的对象
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new Rpc();
        }
        return self::$instance;
    }

    /**
     * 初始化服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function start() {
        self::$instance::runtime();
        self::$instance::ping();
    }

    /**
     * 初始化配置项
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function runtime() {
        $file = ROOT_PATH.'/rpc/map.php';
        self::$config = require $file;
    }

    /**
     * 定时轮询，检测服务的延时
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function ping() {
        \Swoole\Timer::tick(3000, function ($timer_id) {
            $config = self::$config;
            foreach ($config as $k => $v) {
                foreach ($v as $kk => $vv) {
                    foreach ($vv as $key => $val) {
                        // 手动关闭的节点不需要检测
                        if (!empty($val['status'])) continue;

                        $shell = 'ping  -c 1 '.$val['ip'];
                        $arr = \Swoole\Coroutine\System::exec($shell);
                        if ($arr == false) {
                            self::ping_error($k, $kk, $val, 1);
                        } else {
                            $str = $arr['output'];
                            if (stripos($str, 'time=') !== false) {
                                self::$config[$k][$kk][$key]['is_fault'] = 0;
                                $arr = explode('time=', $str);
                                $arr = explode(' ms', $arr[1]);

                                $score = isset($val['score']) ? $val['score'] : 100;
                                $ms = $arr[0];
                                if ($ms > 460) {
                                    $score -= 50;
                                } else if ($ms > 400 && $ms <= 460) {
                                    $score -= 40;
                                } else if ($ms > 300 && $ms <= 400) {
                                    $score -= 30;
                                } else if ($ms > 200 && $ms <= 300) {
                                    $score -= 20;
                                } else if ($ms > 100 && $ms <= 200) {
                                    $score -= 10;
                                } else if ($ms <= 100 && $score < 100) {
                                    $score += 5;
                                }
                                self::$config[$k][$kk][$key]['score'] = $score;
                                self::$config[$k][$kk][$key]['ping_ms'] = $ms;
                            } else {
                                self::$config[$k][$kk][$key]['is_fault'] = 1;
                                self::$config[$k][$kk][$key]['ping_ms'] = 999;
                                self::ping_error($k, $kk, $val, 2);
                            }
                        }
                    }
                }
            }
        });
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
    public static function ping_error($class, $function, $config, $status) {
        $obj = new \lifecycle\rpc_error();
        $obj->run($class, $function, $config, $status);
        return false;
    }

    /**
     * 更新配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 配置KEY
     * @param mixed $val 配置参数
     * @return void
    */
    public static function set($key, $val) {
        if (isset(self::$config[$key])) {
            $config = array_merge(self::$config[$key], $val);
            self::$config[$key] = $config;
        } else {
            self::$config[$key] = $val;
        }
    }

    /**
     * 读取配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 配置KEY
     * @return void
    */
    public static function get($key=null) {
        if ($key) {
            if (isset(self::$config[$key])) {
                return self::$config[$key];
            }
            return false;
        }
        return self::$config;
    }

    /**
     * 删除某个配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 配置KEY
     * @return void
    */
    public static function delete($key) {
        if (isset(self::$config[$key])) {
            unset(self::$config[$key]);
            return true;
        }
        
        return false;
    }

    /**
     * 更新某条配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $class 配置KEY
     * @param string $function 配置KEY
     * @param mixed $val 配置参数
     * @return void
    */
    public static function setOne($class, $function, $val) {
        if (isset(self::$config[$class][$function])) {
            $list = self::$config[$class][$function];
            foreach ($list as $k=>$v) {
                if ($v['title'] == $val['title'] && $v['ip'] == $val['ip'] && $v['port'] == $val['port']) {
                    self::$config[$class][$function][$k] = $val;
                    return true;
                }
            }
        }
        return false;
    }
}