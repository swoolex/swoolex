<?php
/**
 * +----------------------------------------------------------------------
 * 布尔值
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class bools
{
    /**
     * 入口方法
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @param array $data 完整表单
     * @param mixed $param 参数值
     * @param string $rule :后参数
     * @return bool
    */
    public static function run($data, $param, $rule=null) {
        return filter_var($param, FILTER_VALIDATE_BOOLEAN);
    }
}