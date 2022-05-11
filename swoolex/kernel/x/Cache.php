<?php
/**
 * +----------------------------------------------------------------------
 * 缓存驱动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Cache
{
    /**
     * 缓存驱动实例
    */
    private $CacheDriver;
    /**
     * 驱动标识符
    */
    private $DriverName = 'default';
    /**
     * 驱动对应的配置
    */
    private $DriverConfig = [];
    
    /**
     * 选择驱动
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @param string $DriverName 驱动标识符
    */
    public function __construct($DriverName = null) {
        if ($DriverName) {
            $this->store($DriverName);
        } else {
            $this->store($this->DriverName);
        }
    }

    /**
     * 切换驱动
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @param string $DriverName 驱动标识符
     * @return this
    */
    public function store($DriverName) {
        $this->DriverConfig = \x\Config::get('cache.'.$DriverName);
        $class = '\x\cache\\'. ucfirst(strtolower($this->DriverConfig['type']));
        $this->CacheDriver = new $class($this->DriverConfig);
        
        return $this;
    }

    /**
     * 驱动方法注入
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @return object
    */
    public function __call($name, $arguments=[]) {
        return call_user_func_array([$this->CacheDriver, $name], $arguments);
    }
}