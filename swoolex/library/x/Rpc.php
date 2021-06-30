<?php
/**
 * +----------------------------------------------------------------------
 * 微服务-配置调用类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Rpc
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象

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
     * 初始化配置文件
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function start() {
        $file = ROOT_PATH.'/rpc/map.php';
        $config = require_once $file;

        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();

        // 先清空服务
        // A、节点名称隐射表
        $redis->del($redis_key);
        // B、所有节点
        $service_name = [];
        foreach ($config as $k => $v) {
            foreach ($v as $kk => $vv) {
                $table = md5($k.$kk);
                $key = $redis_key.'_'.$table;
                $redis->del($key);
                $service_name[] = $table;
            }
        }

        // 重新设置服务节点
        $redis->set($redis_key, json_encode($service_name, JSON_UNESCAPED_UNICODE));
        
        // 批量设置服务
        foreach ($config as $k => $v) {
            foreach ($v as $kk => $vv) {
                foreach ($vv as $key => $val) {
                    $table = md5($k.$kk);
                    $key = $redis_key.'_'.$table;

                    $data = $val;
                    $data['class'] = $k;
                    $data['function'] = $kk;
                    
                    $redis->lpush($key, json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            }
        }

        $redis->return();
        self::ping();
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
        \Swoole\Timer::tick(20000, function ($timer_id) {
            $redis_key = \x\Config::get('rpc.redis_key');
            $redis = new \x\Redis();

            // 读取全部服务
            $json = $redis->get($redis_key);
            $list = $json ? json_decode($json, true) : [];

            foreach ($list as $key) {
                // 服务检测
                $key = $redis_key.'_'.$key;
                $index = $redis->llen($key);
                for ($i=0; $i<$index; $i++) {
                    $json = $redis->lindex($key, $i);
                    $val = $json ? json_decode($json, true) : [];

                    // 手动关闭的节点不需要检测
                    if (!empty($val['status'])) continue;

                    // 先Ping检测
                    $shell = 'ping  -c 1 '.$val['ip'];
                    $arr = \Swoole\Coroutine\System::exec($shell);
                    if ($arr == false) {
                        self::ping_error($val, 1);
                        continue;
                    }
                    $str = $arr['output'];
                    if (stripos($str, 'time=') !== false) {
                        // 检测是否内网IP
                        $vif = filter_var($val['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                        if ($vif === false) {
                            // 再查看端口是否挂了
                            $shell = 'netstat -anp|grep '.$val['port'];
                            $arr = \Swoole\Coroutine\System::exec($shell);
                            if (empty($arr['output'])) {
                                $val['is_fault'] = 1;
                                $val['ping_ms'] = 999;
                                $redis->lset($key, $i, json_encode($val, JSON_UNESCAPED_UNICODE));
                                self::ping_error($val, 2);
                                continue;
                            }

                            $val['is_fault'] = 0;
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
                            if ($score > 100) $score = 100;
                            $val['score'] = $score;
                            $val['ping_ms'] = $ms;
                            $redis->lset($key, $i, json_encode($val, JSON_UNESCAPED_UNICODE));
                        }
                    } else {
                        $val['is_fault'] = 1;
                        $val['ping_ms'] = 999;
                        $redis->lset($key, $i, json_encode($val, JSON_UNESCAPED_UNICODE));
                        self::ping_error($val, 2);
                    }
                }
            }
            $redis->return();
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
    public static function ping_error($config, $status) {
        $obj = new \other\lifecycle\rpc_error();
        $obj->run($config['class'], $config['function'], $config, $status);
        return false;
    }

    /**
     * 读取配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function get($class, $function) {
        $redis_key = \x\Config::get('rpc.redis_key').'_'.md5($class.$function);
        $redis = new \x\Redis();

        // 读取某个服务
        $index = $redis->llen($redis_key);
        $list = [];
        for ($i=0; $i<$index; $i++) {
            $json = $redis->lindex($redis_key, $i);
            if ($json) {
                $list[] = json_decode($json, true);
            }
        }

        $redis->return();
        return $list;
    }

    /**
     * 更新单条配置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function set($config) {
        $redis_index = $config['redis_index'];
        unset($config['redis_index']);
        $redis_key = \x\Config::get('rpc.redis_key').'_'.md5($config['class'].$config['function']);
        $redis = new \x\Redis();
        $res = $redis->lset($redis_key, $redis_index, json_encode($config, JSON_UNESCAPED_UNICODE));
        $redis->return();
        return $res;
    }
}