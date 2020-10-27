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
use Swoole\Database\RedisConfig;
use Swoole\Database\RedisPool;

class Redis2Pool{
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
     * @return void
    */
    private function __construct() {
        // 读取配置类
        $config = \x\Config::run()->get('redis');
        $this->max = $config['pool_max'];
        $this->config = [
            'host'               => $config['host'],
            'port'               => $config['port'],
            'pwd'                => $config['pwd'],
            'timeout'            => $config['timeout'],
            'dbindex'            => $config['dbindex'],
        ];
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
            self::$instance = new \x\redis\Redis2Pool();
        }
        return self::$instance;
    }

    /**
     * 初始连接池
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
        $this->createRedis();
        $this->count = $this->max;
    }
    
    /**
     * 获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return obj
    */
    public function pop() {
        $this->count--;
        if (!$this->connections) return false;
        return $this->connections->get();
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
        $this->count++;
        if (!$this->connections) return false;
        return $this->connections->put($obj);
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
    public function timing_recovery($workerId) {
        // 5秒更新一次当前Redis连接数
        if (\x\Config::run()->get('redis.is_monitor')) {
            \Swoole\Timer::tick(5000, function () use($workerId) {
                $path = ROOT_PATH.'/env/redis_pool_num.count';
                $json = \Swoole\Coroutine\System::readFile($path);
                $array = [];
                if ($json) {
                    $array = json_decode($json, true);
                }
                $array[$workerId] = $this->count;
                \Swoole\Coroutine\System::writeFile($path, json_encode($array));
                unset($json);
                unset($array);
                unset($path);
            });
        }
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
        $this->connections = new RedisPool((new RedisConfig)
            ->withHost($this->config['host'])
            ->withPort($this->config['port'])
            ->withAuth($this->config['pwd'])
            ->withDbIndex($this->config['dbindex'])
            ->withTimeout($this->config['timeout'])
        , $this->max);
    }
}