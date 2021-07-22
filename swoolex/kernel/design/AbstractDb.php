<?php
/**
 * +----------------------------------------------------------------------
 * DAO对象-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

abstract class AbstractDb {
    /**
     * Mysql连接池实例
    */
    protected $pool;
    /**
     * 连接池类型
    */
    protected $type;
    /**
     * 表前缀
    */
    public $prefix;
    /**
     * SQL构造器反射类
    */
    protected $sql_ref;
    /**
     * SQL构造器
    */
    protected $sql;
    /**
     * 调试模式
    */
    protected $debug;
    /**
     * 是否使用的连接池
    */
    protected $is_pool;
    /**
     * 是否归还了链接
    */
    protected $return_status = false;

    /**
     * 选择连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 连接池标识，不传默认第一个标识
     * @return void
    */
    abstract public function __construct($data=null);

    /**
     * 利用析构函数，防止有漏掉没归还的连接，让其自动回收，减少不规范的开发者
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function __destruct();

    /**
     * 归还连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function return();

    /**
     * 开启事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function begin();

    /**
     * 提交事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function commit();

    /**
     * 回滚事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function rollback();

    /**
     * 执行Query操作
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function query($sql, $status=true);

    /**
     * 执行新增的SQL操作
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function exec($sql);

    /**
     * SQL构造器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function __call($name, $arguments=[]);
}