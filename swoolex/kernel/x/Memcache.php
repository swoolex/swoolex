<?php
/**
 * Created by PhpStorm.
 * User: f
 * Date: 2021/11/4
 * Time: 15:24
 */

namespace x;

class Memcache
{
    /**
     * 连接池实例
     */
    public $Conn;
    /**
     * Memcache采集器
     */
    public $Bulk;
    /**
     * 是否归还了连接
     */
    private $return_status = false;

    /**
     * 初始化实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @deprecated 暂不启用
     * @global 无
     * @return void
     */
    public function __construct() {
        $this->Conn = \x\memcached\Pool::run()->pop();
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
     * @version v2.5.4 + 2021-08-30
     * @deprecated 暂不启用
     * @global 无
     * @return void
     */
    public function return() {
        if ($this->return_status === false) {
            $this->return_status = true;
            return \x\memcached\Pool::run()->free($this->Conn);
        }
        return false;
    }
    /**
     * 获取对应的值
     * @param $key
     * @return bool
     */
    public function get($key)
    {
        if (empty($key)){
            return false;
        }
        return $this->Conn->get($key);
    }

    /**
     * 设置对应的键值对
     * @param $key           对应的key
     * @param $value         对应的值
     * @param int $flag      是否压缩
     * @param int $expire    时间
     * @return bool
     */
    public function set($key,$value,$flag = 0,$expire = 0)
    {
        if (empty($key) ||empty($value)){
            return false;
        }
        return $this->Conn->set($key,$value,$flag = 0,$expire = 0);
    }

    /**
     * 删除对应的键
     * @param $key
     * @param int $timeout
     * @return bool  删除该元素的执行时间。如果值为0,则该元素立即删除，如果值为30,元素会在30秒内被删除。
     */
    public function delete($key,$timeout = 0)
    {
        if (empty($key)){
            return false;
        }
        return $this->Conn->delete($key,$timeout);
    }

    /**
     * 添加对应的额键值对
     * @param $key
     * @param $value
     * @param int $flag
     * @param int $expire
     * @return bool
     */
    public function add($key,$value,$flag = 0,$expire = 0)
    {
        if (empty($key) ||empty($value)){
            return false;
        }
        return $this->Conn->set($key,$value,$flag = 0,$expire = 0);
    }

    /**
     * 增加一个元素的值
     * @param $key
     * @param int $value
     * @return bool
     */
    public function increment($key,$value = 1)
    {
        if (empty($key)){
            return false;
        }
        return $this->Conn->increment($key,$value);
    }
    /**
     * 获取当前机器的版本信息
     * @return mixed
     */
    public function getVersion()
    {
        return $this->Conn->getVersion();
    }
    /**
     * 获取直接的方法名
     * @param $name
     * @param array $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments=[])
    {
        if (!$this->Conn) return false;
        if (empty($name)) return false;

        $ref = new \ReflectionClass($this->Conn);
        $ins = $this->Conn;
        if (!$ref->hasMethod($name)) return false;

        $obj = $ref->getmethod($name);

        return $obj->invokeArgs($ins, $arguments);
    }
}// class end