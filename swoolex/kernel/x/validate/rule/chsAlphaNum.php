<?php
/**
 * +----------------------------------------------------------------------
 * 汉字、字母或数字
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class chsAlphaNum
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
        return preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u", $param);
    }
}