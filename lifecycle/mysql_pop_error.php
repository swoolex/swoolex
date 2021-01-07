<?php
// +----------------------------------------------------------------------
// | 当应用层捕捉到Mysql连接数小于等于0时，会回调至此
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace lifecycle;

class mysql_pop_error
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 连接池类型：read、write、log
     * @return bool
    */
    public function run($type) {
        // 此处可自行实现消息通知
        echo $type.' Mysql 连接数不足！'.PHP_EOL;
        return true;
    }
}