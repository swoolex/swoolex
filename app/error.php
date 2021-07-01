<?php
/**
 * +----------------------------------------------------------------------
 * 自定义404错误页面
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

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
        return $controller->fetch('<html><head><meta charset="UTF-8"><title>HTTP 404</title><meta name="robots"content="noindex,nofollow"/><meta name="viewport"content="width=device-width, initial-scale=1, user-scalable=no"></head><body style="text-align: center;"><h1 style="padding-top: 20%;font-size: 50px;color: #3674ff;margin: 0;">SW-X</h1><h2 style="font-size: 13.5px;color: #6f6f6f;margin: 35px 0;font-weight: 500;">抱歉，您要查看的数据不存在或已被删除。</h3></body></html>', '404');
    }
}