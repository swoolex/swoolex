<?php
// +----------------------------------------------------------------------
// | 自定义404错误页面
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app;

class error
{
    
    /**
     * 入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param \x\Controller $controller
     * @return void
    */
    public function __construct($controller) {
        return $controller->fetch('页面错误啦', '404');
    }
}