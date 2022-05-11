<?php
/**
 * +----------------------------------------------------------------------
 * 队列消费端
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Queue 
{
    /**
     * 驱动实例
    */
    private $QueueDriver;
    /**
     * 驱动
    */
    private $DriverName = 'default';

    /**
     * 初始化配置
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
    */
    public function __construct($DriverName = null) {
        if ($DriverName !== null) $this->DriverName = $DriverName;
    }

    /**
     * 指定队列
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @param string $type 队列标识符
     * @return this
    */
    public function store($type) {
        $this->DriverName = $type;
        return $this;
    }

    /**
     * 驱动方法注入
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return object
    */
    public function __call($name, $arguments=[]) {
        $this->saveConfig();
        return call_user_func_array([$this->QueueDriver, $name], $arguments);
    }

    /**
     * 更新驱动实例
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
    */
    private function saveConfig() {
        $config = \x\Config::get('queue.'.$this->DriverName);
        $class = '\x\queue\driver\\'. ucfirst(strtolower($config['type']));
        $this->QueueDriver = new $class($config);
    }
}