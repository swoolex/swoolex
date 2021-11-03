<?php
/**
 * +----------------------------------------------------------------------
 * Redis存储 - 缓存驱动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\cache;
use design\AbstractCacheDriver;

class Redis extends AbstractCacheDriver
{
    /**
     * Redis实例
    */
    private $Redis;

    /**
     * 初始化配置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param array $config
     * @return void
    */
    public function __construct($config) {
        $this->config = $config;
        $this->Redis = new \x\Redis();
    }

    /**
     * 释放实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __destruct() {
        $this->Redis->return();
    }
    
    /**
     * 设置缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @param mixed $val 值
     * @param int $prefix 有效期(S)
     * @return void
    */
    public function set($key, $val, $prefix=null) {
        $key = $this->config['prefix'].$key;
        if (is_null($prefix)) $prefix = $this->config['expire'];
        if (!is_numeric($val)) {
            $val = $this->_encode($val);
        } 
        if ($prefix) {
            return $this->Redis->setex($key, $prefix, $val);
        } 
        return $this->Redis->set($key, $val);
    }

    /**
     * 缓存是否有效存在
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return bool
    */
    public function has($key) {
        $key = $this->config['prefix'].$key;
        $val = $this->Redis->get($key);
        if ($val !== false) return false;

        return true;
    }

    /**
     * 获取缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return mixed
    */
    public function get($key) {
        $key = $this->config['prefix'].$key;
        $val = $this->Redis->get($key);
        if ($val !== false) {
            if (!is_numeric($val)) {
                $val = $this->_decode($val);
            } 
            return $val;
        }
        return false;
    }

    /**
     * 数字类型自增
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @param int $num 自增值
     * @return mixed
    */
    public function inc($key, $num=1) {
        $key = $this->config['prefix'].$key;
        return $this->Redis->incrby($key, $num);
    }

    /**
     * 数字类型自减
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @param int $num 自减值
     * @return mixed
    */
    public function dec($key, $num=1) {
        $key = $this->config['prefix'].$key;
        return $this->Redis->decrby($key, $num);
    }

    /**
     * 删除缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return bool
    */
    public function rm($key) {
        $key = $this->config['prefix'].$key;
        return $this->Redis->del($key);
    }

    /**
     * 删除缓存并获取
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return mixed
    */
    public function pull($key) {
        $val = $this->get($key);
        if ($val === false) return false;
        if ($this->rm($key) === false) return false;
        
        return $val;
    }
}
