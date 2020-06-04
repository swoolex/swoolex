<?php
// +----------------------------------------------------------------------
// | Redis连接池
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\redis;

class RedisPool{
     /**
     * 最少连接数
    */
    protected $min;
    /**
     * 最大连接数
    */
    protected $max;
    /**
     * 当前连接数
    */
    protected $count;
    /**
     * 连接池组
    */
    protected $connections;
    /**
     * 配置项
    */
    protected $config;
    /**
     * 创建静态对象变量,用于存储唯一的对象实例  
    */
    protected static $instance = null;

    /**
     * 私有化克隆函数，防止外部克隆对象
    */
    private function __clone() {}

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $
     * @param mixed $
     * @return void
    */
    private function __construct() {
        // 读取配置类
        $config = \x\Config::run()->get('redis');
        $this->min = $config['pool_min'];
        $this->max = $config['pool_max'];
        $this->spareTime = $config['pool_spare_time'];
        $this->config = [
            'host'               => $config['host'],
            'port'               => $config['port'],
            'pwd'                => $config['pwd'],
            'connect_timeout'    => $config['connect_timeout'],
            'timeout'            => $config['timeout'],
            'serialize'          => $config['serialize'],
            'reconnect'          => $config['reconnect'],
            'compatibility_mode' => $config['compatibility_mode'],
        ];
        $this->connections = new \Swoole\Coroutine\Channel($this->max + 1);
    }

    /**
     * 单例入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function run() {
        # 只有第一次调用，才允许创建对象实例
        if (empty(self::$instance)) {
            self::$instance = new \x\redis\RedisPool();
        }
        return self::$instance;
    }

    /**
     * 初始换最小数量连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $
     * @param mixed $
     * @return $this|null
    */
    public function init() {
        for ($i=0; $i<$this->min; $i++) {
            # 创建
            $obj = $this->create($i);
            $this->count++;
            # 写入消息队列-支持协程
            $this->connections->push($obj);
        }
        
        return $this;
    }
    /**
     * 创建出消息队列的内部结构
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return array|null
    */
    protected function create() {
        $obj = null;
        # 创建连接实例
        $redis = $this->createRedis();
        if ($redis) {
            $obj = [
                'last_used_time' => time(),
                'redis' => $redis,
            ];
        }
        return $obj;
    }
    
    /**
     * 获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $timeOut 出队最大等待时间
     * @return obj
    */
    public function pop($timeOut = 3) {
        $obj = null;
        if ($this->connections->isEmpty()) {
            // 连接数没达到最大，新建连接入池
            if ($this->count < $this->max) {
                $this->count++;
                $obj = $this->create();
            } else {
                // timeout为出队的最大的等待时间
                $obj = $this->connections->pop($timeOut);
            }
        } else {
            $obj = $this->connections->pop($timeOut);
        }
        return $obj;
    }

    /**
     * 归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function free($obj) {
        if ($obj) {
            $obj['last_used_time'] = time();
            $this->connections->push($obj);
        }
    }
    
    /**
     * 定时回收空闲连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function timing_recovery($time) {
        // 大约10分钟检测一次连接
        \Swoole\Timer::tick($time * 1000, function () {
            # 先检测读 - 连接
            $list = [];
            # 一半最大进程为界限
            if ($this->connections->length() < intval($this->max * 0.5)) {
                echo "请求连接数还比较多，暂不回收空闲连接\n";
            }
            # 堵塞循环
            while (true) {
                if (!$this->connections->isEmpty()) {
                    $obj = $this->connections->pop(0.001);
                    # 拿出最近一次使用时间
                    $last_used_time = $obj['last_used_time'];
                    # 判断回收超期时间
                    if ($this->count > $this->min && (time() - $last_used_time > $this->spare_time)) {
                        $this->count--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->connections->push($item);
            }
            unset($list);
        });
    }
    
    /**
     * 清空连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function clean() {
        while (true) {
            if (!$this->connections->isEmpty()) {
                $obj = $this->connections->pop(0.001);
                $this->count--;
            } else {
                break;
            }
        }
    }

    /**
     * 创建数据库连接实例
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return swoole_redis
    */
    protected function createRedis() {
        // 协程Redis连接支持
        # 启动redis
        $redis = new \Swoole\Coroutine\Redis();
        $redis->connect($this->config['host'], $this->config['port']);
        # 设置Swoole-Redis配置
        $redis->setOptions([
            'connect_timeout' => $this->config['connect_timeout'],
            'timeout' => $this->config['timeout'],
            'serialize' => $this->config['serialize'],
            'reconnect' => $this->config['reconnect'],
            'compatibility_mode' => $this->config['compatibility_mode'],
        ]);
        # 如果有密码
        if ($this->config['pwd']) {
            $redis->auth($this->config['pwd']);
        }
        if (!$redis->connected) {
            echo 'redis链接失败：'.$redis->errCode.'，msg：'.$redis->errMsg;
            return null;
        }

        return $redis;
    }
}