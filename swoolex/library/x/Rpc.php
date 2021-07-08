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
        
        /**
         * 需要以下：
         * 1、一个list，记录所有redis-key的名称，用于重启服务时初始化服务
         * 2、class+function为key名的sets，记录下面全部class+function+ip+port的key
         * 3、class+function+ip+port为key名的hash，记录一个节点的详情，不包含score、使用人数、包含延迟ping值
         * 4、class+function+ip+port+【score】，记录某个节点的当前评分值
         * 5、class+function+ip+port+【peaks】，记录某个节点的当前峰值人数
         * 6、class+function+ip+port+【num】，记录某个节点的历史使用人数
        */ 
        // 先清空服务
        // A、RPC-KEY名称隐射表
        while($key = $redis->LPOP($redis_key)) {
            $redis->del($redis_key.$key);
        }
        // B、重新初始化全部配置
        // $k  路由名称
        // $kk 方法名称
        foreach ($config as $k => $v) {
            foreach ($v as $kk => $vv) {
                foreach ($vv as $key => $val) {
                    $sets_key = '_sets_'.md5($k.$kk);
                    $md5 = md5($k.$kk.$val['ip'].$val['port']);
                    $hash_key = '_hash_'.$md5;
                    // 记录key名
                    $redis->SADD($redis_key.$sets_key, $hash_key);
                    // 记录节点详情
                    $data = $val;
                    $data['class'] = $k;
                    $data['function'] = $kk;
                    $redis->HMSET($redis_key.$hash_key, $data);

                    $score_key = '_score_'.$md5;
                    $peaks_key = '_peaks_'.$md5;
                    $num_key = '_num_'.$md5;
                    // 初始化
                    $redis->set($redis_key.$score_key, 100);
                    $redis->set($redis_key.$peaks_key, 0);
                    $redis->set($redis_key.$num_key, 0);

                    // KEY写入记录表
                    $redis->LPUSH($redis_key, $sets_key);
                    $redis->LPUSH($redis_key, $hash_key);
                    $redis->LPUSH($redis_key, $score_key);
                    $redis->LPUSH($redis_key, $peaks_key);
                    $redis->LPUSH($redis_key, $num_key);
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
            $max = $redis->LLEN($redis_key);
            for ($i=0; $i<$max; $i++) {
                $key = $redis->LINDEX($redis_key, $i);
                if (strpos($key, '_hash_') !== false) {
                    $val = $redis->hGetAll($redis_key.$key);
                    // 空的跳过
                    if (empty($val)) continue;
                    // 手动关闭的节点不需要检测
                    if (!empty($val['status'])) continue;
                    $md5 = md5($val['class'].$val['function'].$val['ip'].$val['port']);
                    $score_key = '_score_'.$md5;
                    $peaks_key = '_peaks_'.$md5;

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
                                $redis->HMSET($redis_key.$key, $val);
                                $redis->SET($redis_key.$peaks_key, 999);
                                self::ping_error($val, 2);
                                continue;
                            }

                            $val['is_fault'] = 0;
                            $arr = explode('time=', $str);
                            $arr = explode(' ms', $arr[1]);
                            $ms = $arr[0];
                            $score = $redis->get($redis_key.$score_key);
                            if ($ms > 460) {
                                $redis->DECRBY($redis_key.$score_key, 50);
                            } else if ($ms > 400 && $ms <= 460) {
                                $redis->DECRBY($redis_key.$score_key, 40);
                            } else if ($ms > 300 && $ms <= 400) {
                                $redis->DECRBY($redis_key.$score_key, 30);
                            } else if ($ms > 200 && $ms <= 300) {
                                $redis->DECRBY($redis_key.$score_key, 20);
                            } else if ($ms > 100 && $ms <= 200) {
                                $redis->DECRBY($redis_key.$score_key, 10);
                            } else if ($ms <= 100 && $score < 100) {
                                $redis->INCRBY($redis_key.$score_key, 5);
                            } else if ($score >= 100) {
                                $redis->DECRBY($redis_key.$score_key, ($score-100));
                            }
                            $redis->SET($redis_key.$peaks_key, $ms);
                            $redis->HMSET($redis_key.$key, $val);
                        }
                    } else {
                        $val['is_fault'] = 1;
                        $redis->HMSET($redis_key.$key, $val);
                        $redis->SET($redis_key.$peaks_key, 999);
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
        $redis_key = \x\Config::get('rpc.redis_key');
        $set_key = '_sets_'.md5($class.$function);
        $redis = new \x\Redis();

        $array = $redis->SMEMBERS($redis_key.$set_key);
        $list = [];
        // 读取全部节点
        foreach ($array as $key) {
            $val = $redis->hGetAll($redis_key.$key);
            if ($val) {
                $md5 = md5($val['class'].$val['function'].$val['ip'].$val['port']);
                $score_key = '_score_'.$md5;
                $peaks_key = '_peaks_'.$md5;
                $num_key = '_num_'.$md5;
                
                $val['ping_ms'] = $redis->get($redis_key.$peaks_key);
                $val['score'] = $redis->get($redis_key.$score_key);
                $val['request_num'] = $redis->get($redis_key.$num_key);
                
                $list[] = $val;
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
        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();

        $md5 = md5($config['class'].$config['function'].$config['ip'].$config['port']);
        $hash_key = '_hash_'.$md5;

        $res = $redis->HMSET($redis_key.$hash_key, $config);
        $redis->return();
        return $res;
    }
}