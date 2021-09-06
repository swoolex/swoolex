<?php
/**
 * +----------------------------------------------------------------------
 * 单例-基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;


trait AbstractSingleCase
{
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021.09.06
     * @deprecated 暂不启用
     * @global 无
     * @return static
    */
    public static function run(...$params){
        if (empty(self::$instance)) {
            self::$instance = new static(...$params);
        }

        return self::$instance;
    }
}