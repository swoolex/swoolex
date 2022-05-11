<?php
/**
 * +----------------------------------------------------------------------
 * 上周
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db\mysql\query;

class lastweek {
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
        $w = date('w');
        if ($w == 0) $w = 7;
        
        $ww = $w-1;//开始减的天数
        $rw =7-$w;//结束加的天数

        //上周
        $lww = $ww+7;//开始减的天数
        $lrw = $rw-7;//结束加的天数

        $start = strtotime(date('Y-m-d',strtotime("-{$lww} days")));
        $end = strtotime(date('Y-m-d',strtotime("+{$lrw} days")))+86399;
        
        return '('.$field.' >= '.$start.' AND '.$field.' < '.$end.')';
    }
}