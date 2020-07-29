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
     * 连接数
    */
    protected $read;
    /**
     * 当前连接数
    */
    protected $read_count;
    /**
     * 连接池组
    */
    protected $read_connections;
    /**
     * 数据库配置
    */
    protected $read_database;

    //--------------------------------------- 写入数据库连接 ----------------------------------------
    /**
     * 连接数
    */
    protected $write;
    /**
     * 当前连接数
    */
    protected $write_count;
    /**
     * 连接池组
    */
    protected $write_connections;
    /**
     * 数据库配置
    */
    protected $write_database;

    //--------------------------------------- 日志数据库连接 ----------------------------------------
    /**
     * 连接数
    */
    protected $log;
    /**
     * 当前连接数
    */
    protected $log_count;
    /**
     * 连接池组
    */
    protected $log_connections;
    /**
     * 数据库配置
    */
    protected $log_database;


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
    public abstract function read_pop();
    public abstract function read_free($obj);
    public abstract function write_pop();
    public abstract function write_free($obj);
    public abstract function log_pop();
    public abstract function log_free($obj);
    protected abstract function createDb($database, $size);

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
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
        $this->read = $config['pool_read'];
        $this->read_database = $config['pool_read_database'];
        # 写
        $this->write = $config['pool_write'];
        $this->write_database = $config['pool_write_database'];
        # 日志
        $this->log = $config['pool_log'];
        $this->log_database = $config['pool_log_database'];
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
}