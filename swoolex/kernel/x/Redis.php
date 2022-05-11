<?php
/**
 * +----------------------------------------------------------------------
 * Redis操作类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Redis {
    /**
     * Mysql连接池实例
    */
    private $pool;
   
    /**
     * SQL构造器反射类
    */
    private $sql_ref;
    /**
     * 连接池类型
    */
    public $type;
    /**
     * 前缀
    */
    private $prefix;
    /**
     * 是否归还了链接
    */
    private $return_status = false;
    
    /**
     * 选择连接池
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param string $data 连接池标识，不传默认第一个标识
    */
    public function __construct($data=null) {
        $arr = \x\Config::run()->get('redis.pool_list');
        if (empty($data)) {
            $data = key($arr);
        }
        $this->type = $data;
        # 获取前缀
        $this->prefix = $arr[$this->type]['table'];
        $this->pool = \x\redis\Pool::run()->pop($data);
        // 选择默认数据库
        $this->select($arr[$this->type]['dbindex']);
    }

    /**
     * 利用析构函数，防止有漏掉没归还的连接，让其自动回收，减少不规范的开发者
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
    */
    public function __destruct() {
        if ($this->return_status === false) {
            $this->return();
        }
    }

    /**
     * 归还连接池
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return bool
    */
    public function return() {
        $this->return_status = true;
        return \x\redis\Pool::run()->free($this->type, $this->pool);
    }
    
    /**
     * 手动修改表前缀
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @param string $prefix
     * @return this
    */
    public function prefix($prefix) {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 事件注入
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return false|\Redis
    */
    public function __call($name, $arguments=[]) {
        if (!$this->pool) return false;
        if (empty($name)) return false;

        $ref = new \ReflectionClass($this->pool);
        $ins = $this->pool;
        if (!$ref->hasMethod($name)) return false;

        // 加上前缀
		if ($name == 'rawCommand') {
			if (isset($arguments[1])) {
                if ($arguments[0] != 'select') $arguments[1] = $this->prefix.$arguments[1];
			}
		} else {
			if (isset($arguments[0]) && $name != 'select') {
                if (!is_array($arguments[0])) {
                    $arguments[0] = $this->prefix.$arguments[0];
                }
			}
        }

        $obj = $ref->getmethod($name);
        return $obj->invokeArgs($ins, $arguments);
    }

    // 修复scan方法
    public function sScan($key, &$iterator, $pattern = '', $count = 0) {
        return $this->pool->sScan($this->prefix.$key,$iterator,$pattern,$count);
    }
    public function scan( &$iterator, $pattern = null, $count = 0 ) {
        return $this->pool->scan($iterator,$pattern,$count);
    }
    public function zScan($key, &$iterator, $pattern = '', $count = 0) {
        return $this->pool->zScan($this->prefix.$key,$iterator,$pattern,$count);
    }
    public function hScan($key, &$iterator, $pattern = '', $count = 0) {
        return $this->pool->hScan($this->prefix.$key,$iterator,$pattern,$count);
    }

}