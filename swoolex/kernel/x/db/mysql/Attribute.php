<?php
/**
 * +----------------------------------------------------------------------
 * Mysql-SQL-ORM-配置参数
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db\mysql;

class Attribute {

    /**
     * 表名
    */
    public $table;
    /**
     * 别名
    */
    public $alias;
    /**
     * 条件
    */
    public $where;
    /**
     * 字段
    */
    public $field = '*';
    /**
     * 记录数
    */
    public $limit;
    /**
     * 分页记录数
    */
    public $page;
    /**
     * 排序
    */
    public $order;
    /**
     * 筛选
    */
    public $having;
    /**
     * 分组
    */
    public $group;
    /**
     * 链表
    */
    public $join;
    /**
     * 表前缀
    */
    public $prefix;
    /**
     * TestCase唯一别名
    */
    public $test_case;
    /**
     * 不执行sql
    */
    public $debug = false;
}