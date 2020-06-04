<?php
// +----------------------------------------------------------------------
// 上月
// +----------------------------------------------------------------------
// Copyright (c) 2020 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\db\query;

class lastmonth {
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
        $time = strtotime("-1 month");
        $month_start = date('Y-m-01', $time);
        $month_end = date('Y-m-t', $time);

        $start = strtotime($month_start);
        $end = strtotime($month_end)+86399;
        
        return '('.$field.' >= '.$start.' AND '.$field.' < '.$end.')';
    }
}