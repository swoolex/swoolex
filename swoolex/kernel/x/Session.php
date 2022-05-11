<?php
/**
 * +----------------------------------------------------------------------
 * Session类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Session {
    /**
     * 前缀
    */
    private static $session_prefix;
    /**
     * 过期时间
    */
    private static $session_outtime;
    /**
     * SessionID
    */
    private static $session_id;

    /**
     * 是否存在
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $key
     * @return bool
    */
    public static function has($key) {
        self::config();
        $redis = new \x\Redis();

        $key = self::$session_id.'_'.self::$session_prefix.$key;
        if (!empty($redis->get($key))) {
            $redis->return();
            return true;
        }

        $redis->return();
        return false;
    }

    /**
     * 读取
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $key
     * @return mixed
    */
    public static function get($key) {
        self::config();
        $redis = new \x\Redis();

        $key = self::$session_id.'_'.self::$session_prefix.$key;
        if (!empty($redis->get($key))) {
            $data = json_decode($redis->get($key), true);
            $redis->return();
            return $data;
        }

        $redis->return();
        return false;
    }

    /**
     * 写入
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $key
     * @param int $val
     * @param time $time
     * @return bool
    */
    public static function set($key, $val, $time=null) {
        self::config();
        $redis = new \x\Redis();

        $key = self::$session_id.'_'.self::$session_prefix.$key;
        if (empty($time)) {
            $time = self::$session_outtime;
        }
        $time += time();

        $val = json_encode($val);

        $redis->set($key, $val);
        $redis->expireat($key, $time);
        $redis->return();

        return true;
    }

    /**
     * 删除
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param string $key
     * @return bool
    */
    public static function delete($key) {
        self::config();
        $redis = new \x\Redis();
        $key = self::$session_id.'_'.self::$session_prefix.$key;

        if ($redis->del($key)) {
            $redis->return();
            return true;
        }

        $redis->return();
        return false;
    }

    /**
     * 清空
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return bool
    */
    public static function clear() {
        self::config();
        $redis = new \x\Redis();
        $key = self::$session_id.'_'.self::$session_prefix.'*';
        $list = $redis->keys($key);
        $ret = $redis->del($list);
        $redis->return();
        return $ret;
    }

    /**
     * 初始化配置
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
    */
    private static function config() {
        $Request = \x\context\Request::get();
        self::$session_id = $Request->cookie['PHPSESSID'];
        self::$session_prefix = \x\Config::get('app.session_prefix');
        self::$session_outtime = \x\Config::get('app.session_outtime');
    }
}