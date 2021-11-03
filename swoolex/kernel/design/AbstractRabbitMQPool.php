<?php
/**
 * +----------------------------------------------------------------------
 * RabbitMQ连接池 - 抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

abstract class AbstractRabbitMQPool {
    /**
     * 配置项
    */
    protected $config;
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
    public abstract function pop($timeOut);
    public abstract function free($obj);
    protected abstract function create();

    /**
     * 初始化参数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function __construct() {
        // 读取配置类
        $this->config = \x\Config::get('rabbitmq');
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
            self::$instance = new \x\rabbitmq\Pool();
        }
        return self::$instance;
    }
}