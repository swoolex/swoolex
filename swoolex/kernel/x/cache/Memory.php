<?php
/**
 * +----------------------------------------------------------------------
 * 内存存储 - 缓存驱动
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
use x\cache\parts\Memory as Parts;

class Memory extends AbstractCacheDriver
{
    /**
     * 缓存组件实例
    */

    /**
     * 初始化配置
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param array $config
    */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * 设置缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $val 值
     * @param int $prefix 有效期(S)
     * @return bool
    */
    public function set($key, $val, $prefix=null) {
        $key = $this->config['prefix'].$key;
        if (is_null($prefix)) $prefix = $this->config['expire'];

        return Parts::run()->write($key, $val, $prefix);
    }

    /**
     * 缓存是否有效存在
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return bool
    */
    public function has($key) {
        $key = $this->config['prefix'].$key;
        return Parts::run()->has($key);
    }

    /**
     * 获取缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $default 不存在时默认返回值
     * @return mixed
    */
    public function get($key, $default=null) {
        $key = $this->config['prefix'].$key;
        $ret = Parts::run()->read($key);

        if ($ret !== false) return $ret;
        if ($default !== null ) return $default;

        return false;
    }

    /**
     * 数字类型自增
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自增值
     * @return mixed
    */
    public function inc($key, $num=1) {
        $key = $this->config['prefix'].$key;
        return Parts::run()->inc($key, $num);
    }

    /**
     * 数字类型自减
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自减值
     * @return mixed
    */
    public function dec($key, $num=1) {
        $key = $this->config['prefix'].$key;
        return Parts::run()->dec($key, $num);
    }

    /**
     * 删除缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return bool
    */
    public function rm($key) {
        $key = $this->config['prefix'].$key;
        return Parts::run()->delete($key);
    }

    /**
     * 删除缓存并获取
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
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
