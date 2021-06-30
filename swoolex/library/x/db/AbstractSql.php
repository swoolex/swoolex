<?php
/**
 * +----------------------------------------------------------------------
 * 抽象数据库构造器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db;

abstract class AbstractSql {
    /**
     * 必须要实现的抽象
    */
    abstract public function name($table); // 选择表
    abstract public function table($table); // 选择表
    abstract public function alias($as); // 别名
    abstract public function where($filed, $operator, $value); // 条件
    abstract public function field($field); // 字段
    abstract public function limit($left, $right); // 指定条数
    abstract public function page($left, $right); // 分页数
    abstract public function order($order); // 排序
    abstract public function having($field); // 筛选
    abstract public function group($field); // 分组
    abstract public function join($table, $on, $join); // 连表
    abstract public function select(); // 批量查询
    abstract public function find(); // 查询一条
    abstract public function delete(); // 删除
    abstract public function update($data); // 修改
    abstract public function insert($data); // 新增
    abstract public function setInc($field, $num); // 自增
    abstract public function setDec($field, $num); // 自减
    abstract public function buildSql(); // 子查询构造器
    abstract public function whereTime($field, $where, $data); // 时间查询
    abstract public function query($sql); // 原生SQL
    abstract public function debug(); // 不执行SQL
}