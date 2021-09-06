<?php
/**
 * +----------------------------------------------------------------------
 * 具体操作类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\swoole\table;
use design\AbstractSingleCase;
use x\swoole\table\Mirror;

class Action 
{
    use AbstractSingleCase;
    
    /**
     * 表名
    */
    private $table = null;
    /**
     * key名
    */
    private $key = null;

    /**
     * 设置表名
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @deprecated 暂不启用
     * @global 无
     * @param string $table 表名
     * @return void
    */
    public function table($table) {
        $this->table = $table;
        return $this;
    }

    /**
     * 设置key名
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 键名
     * @return void
    */
    public function name($key) {
        $this->key = $key;
        return $this;
    }
    // ----------------------------- 以下为操作类实体类名 ------------------------------
    
    public function createTable($size, $field=[]) {
        if (!$this->table) return false;

        return Mirror::createTable($this->table, $size, $field);
    }

    public function hasTable() {
        if (!$this->table) return false;
        return Mirror::hasTable($this->table);
    }

    public function upsert($data) {
        if (!$this->table) return false;
        if (!$this->key) return false;
        return Mirror::upsert($this->table, $this->key, $data);
    }

    public function has($key=null) {
        if (!$this->table) return false;
        if ($key) $this->key = $key;
        if (!$this->key) return false;

        return Mirror::has($this->table, $this->key);
    }

    public function setInc($field, $num=1) {
        if (!$this->table) return false;
        if (!$this->key) return false;

        return Mirror::setInc($this->table, $this->key, $field, $num);
    }

    public function setDec($field, $num=1) {
        if (!$this->table) return false;
        if (!$this->key) return false;

        return Mirror::setDec($this->table, $this->key, $field, $num);
    }

    public function find($key=null) {
        if (!$this->table) return false;
        if ($key) $this->key = $key;
        if (!$this->key) return false;

        return Mirror::find($this->table, $this->key);
    }

    public function delete($key=null) {
        if (!$this->table) return false;
        if ($key) $this->key = $key;
        if (!$this->key) return false;

        return Mirror::delete($this->table, $this->key);
    }

    public function count() {
        if (!$this->table) return false;

        return Mirror::count($this->table);
    }
}