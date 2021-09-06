<?php
/**
 * +----------------------------------------------------------------------
 * Swoole\Atomic封装
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\swoole\atomic;
use design\AbstractSingleCase;

class Action 
{
    use AbstractSingleCase;
    
    /**
     * 计数器的集合
    */
    private $list = [];

    /**
     * 判断一个计数器是否存在
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @return bool
    */
    public function has($key) {
        return isset($this->list[$key]);
    }

    /**
     * 创建一个计数器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @param int $num 初始值
     * @return bool
    */
    public function create($key, $num=0) {
        if ($this->has($key)) return false;

        $this->list[$key] = new \Swoole\Atomic($num);
        return true;
    }
    
    /**
     * 销毁一个计数器
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @return bool
    */
    public function delete($key) {
        if (!$this->has($key)) return false;

        unset($this->list[$key]);
        return true;
    }
    
    /**
     * 计数器自增
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @param int $num 自增整数
     * @return bool
    */
    public function setInc($key, $num=1) {
        if (!$this->has($key)) return false;

        $this->list[$key]->add($num);
        return true;
    }
    
    /**
     * 计数器自减
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @param int $num 自增整数
     * @return bool
    */
    public function setDec($key, $num=1) {
        if (!$this->has($key)) return false;

        $this->list[$key]->sub($num);
        return true;
    }
    
    /**
     * 获取计数器当前值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @return int|false
    */
    public function get($key) {
        if (!$this->has($key)) return false;

        return $this->list[$key]->get();
    }
    
    /**
     * 直接重置计数器当前值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @param int $num 自增整数
     * @return bool
    */
    public function set($key, $num=0) {
        if (!$this->has($key)) return false;

        $this->list[$key]->set($num);
        return true;
    }
    
    /**
     * 比较计数器当前值，通过则设置值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @param int $num 自增整数
     * @return bool
    */
    public function cmpset($key, $cmp, $num=0) {
        if (!$this->has($key)) return false;

        return $this->list[$key]->cmpset($cmp, $num);
    }

    /**
     * 获取当前服务中的计数器个数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public function count() {
        return count($this->list);
    }
}