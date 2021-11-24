<?php
/**
 * +----------------------------------------------------------------------
 * 敏感词组件-构建哈希表
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\common;

class HashMap
{
    /**
     * 表容器
    */
    protected $hashTable = [];

    /**
     * 添加一个键值对
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @param mixed $value
     * @return void
    */
    public function put($key, $value)
    {
        if (!array_key_exists($key, $this->hashTable)) {
            $this->hashTable[$key] = $value;
            return null;
        }
        $_temp = $this->hashTable[$key];
        $this->hashTable[$key] = $value;
        return $_temp;
    }

    /**
     * 根据key获取对应的value
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @return void
    */
    public function get($key)
    {
        if (array_key_exists($key, $this->hashTable)) {
            return $this->hashTable[$key];
        }
        return null;
    }

    /**
     * 删除指定key的键值对
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @return void
    */
    public function remove($key)
    {
        $temp_table = array();
        if (array_key_exists($key, $this->hashTable)) {
            $tempValue = $this->hashTable[$key];
            while ($curValue = current($this->hashTable)) {
                if (! (key($this->hashTable) == $key)) {
                    $temp_table[key($this->hashTable)] = $curValue;
                }
                next($this->hashTable);
            }
            $this->hashTable = null;
            $this->hashTable = $temp_table;
            return $tempValue;
        }
        return null;
    }

    /**
     * 获取HashMap的所有键值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public function keys()
    {
        return array_keys($this->hashTable);
    }

    /**
     * 获取HashMap的所有value值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public function values()
    {
        return array_values($this->hashTable);
    }

    /**
     * 将一个HashMap的值全部put到当前HashMap中
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param HashMap $map
     * @return void
    */
    public function putAll($map)
    {
        if (! $map->isEmpty() && $map->size() > 0) {
            $keys = $map->keys();
            foreach ($keys as $key) {
                $this->put($key, $map->get($key));
            }
        }
    }

    /**
     * 移除HashMap中所有元素
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @return true
    */
    public function removeAll()
    {
        $this->hashTable = null;
        return true;
    }

    /**
     * 判断HashMap中是否包含指定的值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $value
     * @return bool
    */
    public function containsValue($value)
    {
        while ($curValue = current($this->hashTable)) {
            if ($curValue == $value) {
                return true;
            }
            next($this->hashTable);
        }
        return false;
    }

    /**
     * 判断HashMap中是否包含指定的键key
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @return void
    */
    public function containsKey($key)
    {
        if (array_key_exists($key, $this->hashTable)) {
            return true;
        }
        return false;
    }

   /**
    * 获取HashMap中元素个数
    * @todo 无
    * @author 小黄牛
    * @version v2.5.12 + 2021-11-23
    * @deprecated 暂不启用
    * @global 无
    * @return void
   */
    public function size()
    {
        return count($this->hashTable);
    }

    /**
     * 判断HashMap是否为空
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public function isEmpty()
    {
        return (count($this->hashTable) == 0);
    }
}
