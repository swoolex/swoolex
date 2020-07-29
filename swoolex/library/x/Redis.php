<?php
// +----------------------------------------------------------------------
// | Redis操作类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Redis
{
    /**
     * Mysql连接池实例
    */
    private $pool;
   
    /**
     * SQL构造器反射类
    */
    private $sql_ref;
    
    /**
     * 选择连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct() {
        $this->pool = \x\redis\Redis2Pool::run()->pop();
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
        return \x\redis\Redis2Pool::run()->free($this->pool);
    }

    /**
     * 事件注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __call($name, $arguments=[]) {
        if (!$this->pool) return false;
        if (empty($name)) return false;

        $ref = new \ReflectionClass($this->pool);
        $ins = $this->pool;
        if (!$ref->hasMethod($name)) return false;

        $obj = $ref->getmethod($name);
        return $obj->invokeArgs($ins, $arguments);
    }
}