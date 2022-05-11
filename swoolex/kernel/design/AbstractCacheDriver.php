<?php
/**
 * +----------------------------------------------------------------------
 * 缓存驱动-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

abstract class AbstractCacheDriver {
    /**
     * 初始配置
    */
    private $config = [];
    
    /**
     * 设置缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $val 值
     * @param int $prefix 有效期(S)
    */
    abstract public function set($key, $val, $prefix=null);

    /**
     * 缓存是否有效存在
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return bool
    */
    abstract public function has($key);

    /**
     * 获取缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $default 不存在时默认返回值
     * @return mixed
    */
    abstract public function get($key, $default=null);

    /**
     * 数字类型自增
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自增值
     * @return mixed
    */
    abstract public function inc($key, $num=1);

    /**
     * 数字类型自减
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自减值
     * @return mixed
    */
    abstract public function dec($key, $num=1);

    /**
     * 删除缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return bool
    */
    abstract public function rm($key);

    /**
     * 删除缓存并获取
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return mixed
    */
    abstract public function pull($key);

    /**
     * 序列化
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $str
     * @return mixed
    */
    protected function _encode($str) {
        return serialize($str);
     }
 
     /**
      * 反序列化
      * @author 小黄牛
      * @version v2.5.8 + 2021-11-02
      * @param string $cache
      * @return void
     */
     protected function _decode($cache) {
         return unserialize($cache);
     }
}