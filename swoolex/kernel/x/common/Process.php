<?php
/**
 * +----------------------------------------------------------------------
 * 向自定义进程发送消息
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common;
use design\AbstractSingleCase;

class Process
{
    use AbstractSingleCase;

    /**
     * 子进程实例表
    */
    private static $_list = [];

    /**
     * 向子进程传递数据
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @deprecated 暂不启用
     * @global 无
     * @param string $process_key 子进程队列名称
     * @param mixed $mixed 消息内容
     * @return void
    */
    public static function write($process_key, $mixed) {
        if (isset(self::$_list[$process_key])) {
            return self::$_list[$process_key]->write($mixed);
        }
        return false;
    }

    /**
     * 注册子进程实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @deprecated 暂不启用
     * @global 无
     * @param string $process_key 子进程队列名称
     * @param Process $process 子进程实例
     * @return void
    */
    public static function register($process_key, $process) {
        self::$_list[$process_key] = $process;
    }
}