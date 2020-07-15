<?php
// +----------------------------------------------------------------------
// | 数据库操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Db
{
    /**
     * Mysql连接池实例
    */
    private $pool;
    /**
     * 连接池类型
    */
    private $type;
    /**
     * SQL构造器反射类
    */
    private $sql_ref;
    /**
     * SQL构造器
    */
    private $sql;
    /**
     * 调试模式
    */
    private $debug;
    
    /**
     * 选择连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 连接池类型select或者log，为空则为写入
     * @return void
    */
    public function __construct($type=null) {
        if (empty($type)) $type = 'create';
        $this->type = $type;

        switch ($type) {
            case 'create':
                $pool = \x\db\MysqlPool::run()->write_pop();
            break;
            case 'select':
                $pool = \x\db\MysqlPool::run()->read_pop();
            break;
            case 'log':
                $pool = \x\db\MysqlPool::run()->log_pop();
            break;
            default:
                return false;
            break;
        }
        if (empty($pool['db'])) {
            return false;
        }

        $this->debug = \x\Config::run()->get('app.de_bug');
        $this->pool = $pool;

        $this->sql_ref = new \ReflectionClass('\x\db\Sql');
        $this->sql = new \x\db\Sql();
        $this->sql->Db = $this;
    }

    /**
     * 归还连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function return() {
        switch ($this->type) {
            case 'create':
                return \x\db\MysqlPool::run()->write_free($this->pool);
            break;
            case 'select':
                return \x\db\MysqlPool::run()->read_free($this->pool);
            break;
            case 'log':
                return \x\db\MysqlPool::run()->log_free($this->pool);
            break;
            default:
                return false;
            break;
        }
        return false;
    }

    /**
     * 开启事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function begin() {
        return $this->pool['db']->begin();
    }

    /**
     * 提交事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function commit() {
        return $this->pool['db']->commit();
    }

    /**
     * 回滚事务
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function rollback() {
        return $this->pool['db']->rollback();
    }

    /**
     * 执行Query操作
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function query($sql) {
        // 开启调试模式，则记录SQL语句
        if ($this->debug) {
            \x\Log::run()->sql($sql);
        }
        return $this->pool['db']->query($sql);
    }

    /**
     * SQL构造器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __call($name, $arguments=[]) {
        if (!$this->sql_ref) return false;
        if (empty($name)) return false;
        if (!$this->sql_ref->hasMethod($name)) return false;

        $obj = $this->sql_ref->getmethod($name);
        $this->sql = $obj->invokeArgs($this->sql, $arguments);
        return $this->sql;
    }
}