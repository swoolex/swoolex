<?php
/**
 * +----------------------------------------------------------------------
 * 上年
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db\mysql\query;

class lastyear {
    /**
     * 构造时间查询
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 时间字段，必须为int类型
     * @param string $where 表达式
     * @param string $data 内容
     * @return string
    */
    public static function run($field, $where, $data) {
        $day = date('Y')-1;
        
        $start = strtotime($day.'-01-01', time());
        $end = strtotime($day.'-12-31', time())+86399;

        return '('.$field.' >= '.$start.' AND '.$field.' < '.$end.')';
    }
}