<?php
/**
 * +----------------------------------------------------------------------
 * Atomic门面
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\swoole;

class Atomic
{
    /**
     * 单例注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.294
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function __callStatic($name, $arguments=[]) {
        if (empty($name)) return false;
        
        $class = "\x\\swoole\\atomic\\Action";
        return call_user_func_array([$class::run(), $name], $arguments);
    }
}