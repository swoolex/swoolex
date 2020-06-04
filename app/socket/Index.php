<?php
// +----------------------------------------------------------------------
// | 测试事件
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\socket;
use x\WebSocket;
/**
 * @Controller(prefix="test")
*/
class Index extends WebSocket
{
    /**
     * @RequestMapping(route="/index", title="action为test/index访问这里")
    */
    public function index() {
        return $this->fetch(200, '描述', []);
    }

    /**
     * @RequestMapping(route="/demo", title="action为test/demo访问这里")
    */
    public function demo() {
        return $this->fetch(301, '描述');
    }
}