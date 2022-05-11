<?php
/**
 * +----------------------------------------------------------------------
 * Mysql连接池 - 抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

abstract class AbstractMysqlPool {
    //--------------------------------------- 读取数据库连接 ----------------------------------------
    /**
     * DB配置项
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
     * 必须要实现的抽象
    */
    public abstract function init();
    public abstract function pop($key);
    public abstract function free($key, $obj);
    protected abstract function createDb($database, $size);

    /**
     * 初始化参数
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
    */
    private function __construct() {
        // 读取配置类
        $this->config = \x\Config::get('mysql.pool_list');
    }

    /**
     * 单例入口
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @return this
    */
    public static function run() {
        # 只有第一次调用，才允许创建对象实例
        if (empty(self::$instance)) {
            self::$instance = new \x\db\mysql\Pool();
        }
        return self::$instance;
    }
}