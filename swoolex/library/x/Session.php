<?php
// +----------------------------------------------------------------------
// | Session类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Session
{
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @return bool
    */
    public static function delete($key) {
        self::config();
        $redis = new \x\Redis();
        $key = self::$session_id.'_'.self::$session_prefix.$key;

        if ($redis->delete($key)) {
            $redis->return();
            return true;
        }

        $redis->return();
        return false;
    }

    /**
     * 清空
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function clear() {
        self::config();
        $redis = new \x\Redis();
        $key = self::$session_id.'_'.self::$session_prefix.'*';
        $list = $redis->keys($key);
        $ret = $redis->delete($list);
        $redis->return();
        return $ret;
    }

    /**
     * 读取配置
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function config() {
        $Request = \x\Container::getInstance()->get('request');
        self::$session_id = $Request->cookie['PHPSESSID'];
        self::$session_prefix = \x\Config::run()->get('app.session_prefix');
        self::$session_outtime = \x\Config::run()->get('app.session_outtime');
    }
}