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
     * 是否归还了链接
    */
    private $return_status = false;
    
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
     * 利用析构函数，防止有漏掉没归还的连接，让其自动回收，减少不规范的开发者
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __destruct() {
        if ($this->return_status === false) {
            $this->return();
        }
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
        $this->return_status = true;
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

        // 加上前缀
        $prefix = \x\Config::run()->get('redis.table');
		if ($name == 'rawCommand') {
			if (isset($arguments[1])) {
				$arguments[1] = $prefix.$arguments[1];
			}
		} else {
			if (isset($arguments[0])) {
                if (!is_array($arguments[0])) {
                    $arguments[0] = $prefix.$arguments[0];
                }
			}
        }

        $obj = $ref->getmethod($name);
        return $obj->invokeArgs($ins, $arguments);
    }
}