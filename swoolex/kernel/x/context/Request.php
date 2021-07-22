<?php
/**
 * +----------------------------------------------------------------------
 * Swoole - Http - 请求对象
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

class Request extends AbstractContext
{
    /**
     * 请求实例
    */
    private static $request = [];

    /**
     * 设置实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Request $request HTTP请求对象
     * @return void
    */
    public static function set($request, $mixed=null) {
        $id = self::getCoroutineId();
        self::$request[$id] = $request;
    }

    /**
     * 获取实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return Swoole\Http\Request
    */
    public static function get($name=null) {
        $id = self::getCoroutineId();
        return self::$request[$id];
    }

    /**
     * 删除实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
    */
    public static function delete($name=null) {
        $id = self::getCoroutineId();
        if (!isset(self::$request[$id])) return false;
        
        unset(self::$request[$id]);
        return true;
    }
}