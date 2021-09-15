<?php
/**
 * +----------------------------------------------------------------------
 * 值必须是指定的格式日期
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class dateFormat
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
        $info = date_parse_from_format($rule, $param);
        return 0 == $info['warning_count'] && 0 == $info['error_count'];
    }
}