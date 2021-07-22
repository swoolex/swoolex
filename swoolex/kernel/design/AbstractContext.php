<?php
/**
 * +----------------------------------------------------------------------
 * 上下文管理-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

abstract class AbstractContext {
    /**
     * 设置对象
    */
    abstract public static function set($obj, $mixed=null);
    /**
     * 读取对象
    */
    abstract public static function get($name=null);
    /**
     * 清空对象
    */
    abstract public static function delete($name=null);

    /**
     * 获取当前进程下的顶级协程ID
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $id 协程父ID
     * @return int
    */
    protected static function getCoroutineId($id = null) {
        if ($id === false) return $id; 

        if ($id === null) $id = \Swoole\Coroutine::getCid();

        $pid = \Swoole\Coroutine::getPcid($id);
        if ($pid < 0) {
            return $id;
        }
        return self::getCoroutineId($pid);
    }
}