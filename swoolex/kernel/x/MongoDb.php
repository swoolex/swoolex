<?php
/**
 * +----------------------------------------------------------------------
 * MongoDb操作类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class MongoDb {
    /**
     * 连接池实例
    */
    public $Mongo;
    /**
     * MongoDB采集器
    */
    public $Bulk;
    /**
     * 是否归还了连接
    */
    private $return_status = false;
    
    /**
     * 初始化实例
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
    */
    public function __construct() {
        $this->Mongo = \x\mongodb\Pool::run()->pop();
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
     * @version v2.5.4 + 2021-08-30
     * @return bool
    */
    public function return() {
        if ($this->return_status === false) {
            $this->return_status = true;
            return \x\mongodb\Pool::run()->free($this->Mongo);
        }
        return false;
    }

    /**
     * SQL构造器注入
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return object
    */
    public function __call($name, $arguments=[]) {
        return call_user_func_array([new \x\mongodb\Sql($this), $name], $arguments);
    }
}