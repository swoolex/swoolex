<?php
/**
 * +----------------------------------------------------------------------
 * 内存存储 - 缓存表组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\cache\parts;
use design\AbstractSingleCase;

class Memory
{
    use AbstractSingleCase;

    /**
     * 缓存表
    */
    private $_list = [];

    /**
     * 写
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @param mixed $val 值
     * @param int $prefix 有效期(S)
     * @return void
    */
    public function write($key, $val, $prefix) {
        $this->_list[$key] = $val;
        if ($prefix) {
            // 过期后自动删除
            \Swoole\Timer::after(($prefix*1000), function() use ($key) {
                unset($this->_list[$key]);
            });
        }
    }

    /**
     * 读
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return mixed
    */
    public function read($key) {
        if (!$this->has($key)) return false;
        return $this->_list[$key];
    }

    /**
     * 缓存检测
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return bool
    */
    public function has($key) {
        return isset($this->_list[$key]);
    }

    /**
     * 自增
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
        if (!$this->has($key)) return false;
        if (!is_numeric($this->_list[$key])) return false;
        $val = $this->_list[$key]+$num;
        $this->_list[$key] = $val;
        return true;
    }

    /**
     * 自减
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
        if (!$this->has($key)) return false;
        if (!is_numeric($this->_list[$key])) return false;
        $val = $this->_list[$key]-$num;
        if ($val < 0) {
            $this->_list[$key] = 0;
        } else {
            $this->_list[$key] = $val;
        }
        return true;
    }

    /**
     * 删除
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-03
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键
     * @return bool
    */
    public function delete($key) {
        if (!$this->has($key)) return false;
        unset($this->_list[$key]);

        return true;
    }
}