<?php
/**
 * +----------------------------------------------------------------------
 * 统一门面
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Facade
{
    /**
     * 单例注入
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.294
     * @return object
    */
    public static function __callStatic($name, $arguments=[]) {
        if (empty($name)) return false;
        
        $class = "\x\\entity\\".str_replace('x\\', '', get_called_class());
        return call_user_func_array([$class::run(), $name], $arguments);
    }
}