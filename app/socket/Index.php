<?php
/**
 * +----------------------------------------------------------------------
 * 示例WebSocket控制器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace app\socket;
use x\WebSocket;
/**
 * @Controller(prefix="test")
*/
class Index extends WebSocket
{
    /**
     * @RequestMapping(route="/index", title="action为test/index访问这里")
     * @Param(name="id", type="string", value="1", empty="true", min="10")
     * @Param(name="pid", value="2")
     * @Ioc(class="\x\Db", name="Db")
    */
    public function index() {
        $list = $this->Db->name('user')->find();
        // $this->Db->return();
        return $this->fetch(200, '描述', []);
    }

    /**
     * @RequestMapping(route="/demo", title="action为test/demo访问这里")
    */
    public function demo() {
        return $this->fetch(301, '描述');
    }
}