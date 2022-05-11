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
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @param Swoole\Http\Response $response HTTP请求对象
    */
    public static function set($response, $mixed=null) {
        $id = self::getCoroutineId();
        self::$response[$id] = $response;
    }

    /**
     * 获取实例
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @return Swoole\Http\Response
    */
    public static function get($name=null) {
        $id = self::getCoroutineId();

        if (!isset(self::$response[$id])) return false;

        return self::$response[$id];
    }

    /**
     * 删除实例
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
    */
    public static function delete($name=null) {
        $id = self::getCoroutineId();
        if (!isset(self::$response[$id])) return false;
        
        unset(self::$response[$id]);
        return true;
    }
}