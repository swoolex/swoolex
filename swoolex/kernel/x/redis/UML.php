<?php
/**
 * +----------------------------------------------------------------------
 * Redis对象建模组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\redis;
use x\redis\uml\Orm;

class UML extends Orm
{
    /**
     * 使用的Redis连接池标识
    */
    protected $driver = 'default';
    /**
     * 使用哪个Redis表存储
    */
    protected $database = 12;
    /**
     * 是否开启回写记录
    */
    protected $timer = false;
    /**
     * 主键字段
    */
    protected $primary = null;
    /**
     * 建模必传对象
    */
    protected $field_rule = [];
    /**
     * 普通查询规则
    */
    protected $query_rule = [
        // 'name' => ['equal'], // 等于查询
        // 'age' => ['range'], // 范围查询
    ];    
    /**
     * geo配置规则
    */
    protected $geo_rule = [
        'longitude' => false, // 经度
        'latitude' => false, // 纬度
    ];
}