<?php
/**
 * +----------------------------------------------------------------------
 * 不等于
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class neq
{
    /**
     * 入口方法
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param array $data 完整表单
     * @param mixed $param 参数值
     * @param string $rule :后参数
     * @return bool
    */
    public static function run($data, $param, $rule=null) {
        if (strpos($rule, '.') !== false) {
            $value = $data;
            $arr = explode('.', str_replace(' ', '', $rule));
            foreach ($arr as $field) {
                if (!isset($value[$field])) {
                    return false;
                }
                $value = $value[$field];
            }
        } else {
            $value = $rule;
        }

        if ($value == $param) return false;

        return true;
    }
}