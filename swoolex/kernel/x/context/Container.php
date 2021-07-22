<?php
/**
 * +----------------------------------------------------------------------
 * Swoole - 兼容容器对象
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\context;

use design\AbstractContext;

class Container extends AbstractContext {
    /**
     * 容器队列
    */
    private static $list = [];

    /**
     * 设置实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $name key名称
     * @param mixed $mixed 内容
     * @return void
    */
    public static function set($name, $mixed=null) {
        $id = self::getCoroutineId();
        self::$list[$id][$name] = $mixed;
    }

    /**
     * 获取实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $name key名称
     * @return mixed
    */
    public static function get($name=null) {
        $id = self::getCoroutineId();
        
        if (!isset(self::$list[$id][$name])) return false;

        return self::$list[$id][$name];
    }

    /**
     * 删除实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @param string $name key名称
     * @global 无
    */
    public static function delete($name=null) {
        $id = self::getCoroutineId();

        if ($name) {
            if (!isset(self::$list[$id][$name])) return false;
            unset(self::$list[$id][$name]);
        } else {
            if (!isset(self::$list[$id])) return false;
            unset(self::$list[$id]);
        }
        
        return true;
    }
}