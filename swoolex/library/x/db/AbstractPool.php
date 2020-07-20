<?php
// +----------------------------------------------------------------------
// | 数据库连接池抽象
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\db;

abstract class AbstractPool {
    //--------------------------------------- 读取数据库连接 ----------------------------------------
    /**
     * 最少连接数
    */
    protected $read_min;
    /**
     * 最大连接数
    */
    protected $read_max;
    /**
     * 当前连接数
    */
    protected $read_count;
    /**
     * 连接池组
    */
    protected $read_connections;
    /**
     * 用于空闲连接回收判断
    */
    protected $read_spareTime;
    /**
     * 数据库配置
    */
    protected $read_database_list;

    //--------------------------------------- 写入数据库连接 ----------------------------------------
    /**
     * 最少连接数
    */
    protected $write_min;
    /**
     * 最大连接数
    */
    protected $write_max;
    /**
     * 当前连接数
    */
    protected $write_count;
    /**
     * 连接池组
    */
    protected $write_connections;
    /**
     * 用于空闲连接回收判断
    */
    protected $write_spareTime;
    /**
     * 数据库配置
    */
    protected $write_database_list;

    //--------------------------------------- 日志数据库连接 ----------------------------------------
    /**
     * 最少连接数
    */
    protected $log_min;
    /**
     * 最大连接数
    */
    protected $log_max;
    /**
     * 当前连接数
    */
    protected $log_count;
    /**
     * 连接池组
    */
    protected $log_connections;
    /**
     * 用于空闲连接回收判断
    */
    protected $log_spareTime;
    /**
     * 数据库配置
    */
    protected $log_database_list;


    /**
     * 创建静态对象变量,用于存储唯一的对象实例  
    */
    protected static $instance = null;

    /**
     * 私有化克隆函数，防止外部克隆对象
    */
    private function __clone() {}

    /**
     * 必须要实现的抽象
    */
    public abstract function init();
    public abstract function read_pop($timeOut);
    public abstract function read_free($obj);
    public abstract function write_pop($timeOut);
    public abstract function write_free($obj);
    public abstract function log_pop($timeOut);
    public abstract function log_free($obj);
    protected abstract function createDb($database);

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
        $config = \x\Config::run()->get('mysql');
        # 读
        $this->read_min = $config['pool_read_min'];
        $this->read_max = $config['pool_read_max'];
        $this->read_spareTime = $config['pool_read_spare_time'];
        $this->read_database_list = $config['pool_read_database'];
        $this->read_connections = new \Swoole\Coroutine\Channel($this->read_max + 1);
        # 写
        $this->write_min = $config['pool_write_min'];
        $this->write_max = $config['pool_write_max'];
        $this->write_spareTime = $config['pool_write_spare_time'];
        $this->write_database_list = $config['pool_write_database'];
        $this->write_connections = new \Swoole\Coroutine\Channel($this->write_max + 1);
        # 日志
        $this->log_min = $config['pool_log_min'];
        $this->log_max = $config['pool_log_max'];
        $this->log_spareTime = $config['pool_log_spare_time'];
        $this->log_database_list = $config['pool_log_database'];
        $this->log_connections = new \Swoole\Coroutine\Channel($this->log_max + 1);
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
            self::$instance = new \x\db\MysqlPool();
        }
        return self::$instance;
    }

    /**
     * 读 - 统一标准创建出消息队列的内部结构
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $i 循环次数
     * @return array|null
    */
    protected function createRead($i) {
        # 获取数据配置长度
        $count = count($this->read_database_list);
        $num = $i+$count;
        # 计算出使用的配置key-循环使用
        $key = $num % $count;

        $obj = null;
        # 创建数据库连接i实例
        $db = $this->createDb($this->read_database_list[$key]);
        if ($db) {
            $obj = [
                'last_used_time' => time(),
                'db' => $db,
            ];
        }
        return $obj;
    }

    /**
     * 写 - 统一标准创建出消息队列的内部结构
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $i 循环次数
     * @return array|null
    */
    protected function createWrite($i) {
        # 获取数据配置长度
        $count = count($this->write_database_list);
        $num = $i+$count;
        # 计算出使用的配置key-循环使用
        $key = $num % $count;
        $obj = null;
        # 创建数据库连接i实例
        $db = $this->createDb($this->write_database_list[$key]);
        if ($db) {
            $obj = [
                'last_used_time' => time(),
                'db' => $db,
            ];
        }
        return $obj;
    }

    /**
     * 写 - 统一标准创建出消息队列的内部结构
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $i 循环次数
     * @return array|null
    */
    protected function createLog($i) {
        # 获取数据配置长度
        $count = count($this->log_database_list);
        $num = $i+$count;
        # 计算出使用的配置key-循环使用
        $key = $num % $count;

        $obj = null;
        # 创建数据库连接i实例
        $db = $this->createDb($this->log_database_list[$key]);
        if ($db) {
            $obj = [
                'last_used_time' => time(),
                'db' => $db,
            ];
        }
        return $obj;
    }
}