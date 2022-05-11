<?php
/**
 * +----------------------------------------------------------------------
 * 在区间内的查询
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mongodb\query;

class between {
    /**
     * 构造时间查询
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @param string $field 时间字段，必须为int类型
     * @param string $where 表达式
     * @param string $data 内容
     * @return string
    */
    public static function run($field, $where, $data) {
        $start = $data[0];
        $end = $data[1];

        if (!is_numeric($start)) $start = strtotime($start);
        if (!is_numeric($end)) $end = strtotime($end);

        $end = $end+86399;
        
        return [
            [
                'field' => $field,
                'where' => '>=',
                'value' => $start,
            ],
            [
                'field' => $field,
                'where' => '<',
                'value' => $end,
            ],
        ];
    }
}