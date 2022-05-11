<?php
/**
 * +----------------------------------------------------------------------
 * Swoole\Table封装
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\swoole\table;

class Mirror {
    /**
     * 表列
    */
    private static $pool;

    /**
     * 创建一个表
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param int $size 表最大行数
     * @param array $field 初始化字段信息
     * @return bool
    */
    public static function createTable($table, $size, $field=[]) {
        if (self::hasTable($table)) return false;

        $objTable = new \Swoole\Table($size);
        
        foreach ($field as $key => $value) {
            $objTable->column($key, $value['type'], ($value['size'] ?? 0));
        }
        $objTable->create();
        self::$pool[$table] = $objTable;
        return true;
    }

    /**
     * 表是否存在
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @return bool
    */
    public static function hasTable($table) {
        return isset(self::$pool[$table]);
    }

    /**
     * 更新插入
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @param array $array 内容
     * @return bool
    */
    public static function upsert($table, $key, $data) {
        if (!self::hasTable($table)) return false;

        self::$pool[$table]->set($key, $data);
        return true;
    }

    /**
     * 判断某个key是否存在
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @return bool
    */
    public static function has($table, $key) {
        if (!self::hasTable($table)) return false;

        return self::$pool[$table]->exist($key);
    }

    /**
     * 自增
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @return bool
    */
    public static function setInc($table, $key, $field, $num=1) {
        if (!self::hasTable($table)) return false;
        if (!self::has($table, $key)) return false;

        return self::$pool[$table]->incr($key, $field, $num);
    }

    /**
     * 自减
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @return bool
    */
    public static function setDec($table, $key, $field, $num=1) {
        if (!self::hasTable($table)) return false;
        if (!self::has($table, $key)) return false;

        return self::$pool[$table]->decr($key, $field, $num);
    }

    /**
     * 读取一行数据
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @return bool|array
    */
    public static function find($table, $key) {
        if (!self::hasTable($table)) return false;

        return self::$pool[$table]->get($key);
    }

    /**
     * 删除一行数据
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @param string $key 主键key
     * @return bool|array
    */
    public static function delete($table, $key) {
        if (!self::hasTable($table)) return false;
        if (!self::has($table, $key)) return false;

        return self::$pool[$table]->del($key);
    }

    /**
     * 获取表的当前数据量
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @return int
    */
    public static function count($table) {
        if (!self::hasTable($table)) return false;

        return self::$pool[$table]->count();
    }

    /**
     * 获取表的全部记录
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string $table 表名
     * @return Swoole/Table
    */
    public static function all($table) {
        if (!self::hasTable($table)) return false;
        
        return self::$pool[$table];
    }
}