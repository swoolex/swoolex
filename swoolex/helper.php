<?php
// +----------------------------------------------------------------------
// | SW-X 助手函数
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------


if (!function_exists('dd')) {
    /**
     * 打印格式化
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $mixed 需要格式化的内容
     * @return string
    */
    function dd($mixed) {
        $obj = new \x\Dump();
        return $obj->run($mixed);
    }
}
