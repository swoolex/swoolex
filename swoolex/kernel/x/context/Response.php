<?php
/**
 * +----------------------------------------------------------------------
 * Swoole - Http - HTTP响应对象
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

class Response extends AbstractContext
{
    /**
     * 请求实例
    */
    private static $response = [];

    /**
     * 设置实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Http\Response $response HTTP请求对象
     * @return void
    */
    public static function set($response, $mixed=null) {
        $id = self::getCoroutineId();
        self::$response[$id] = $response;
    }

    /**
     * 获取实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return Swoole\Http\Response
    */
    public static function get($name=null) {
        $id = self::getCoroutineId();
        return self::$response[$id];
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
        if (!isset(self::$response[$id])) return false;
        
        unset(self::$response[$id]);
        return true;
    }
}