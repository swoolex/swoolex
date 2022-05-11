<?php
/**
 * +----------------------------------------------------------------------
 * Cookie类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Cookie
{
    /**
     * 是否存在
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $key
     * @return bool
    */
    public static function has($key) {
        $Request = \x\context\Request::get();
        $key = \x\Config::get('app.cookies_prefix').$key;
        if (isset($Request->cookie[$key])) return true;
        
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
        $Request = \x\context\Request::get();
        $key = \x\Config::get('app.cookies_prefix').$key;
        if (isset($Request->cookie[$key]) == false) return false;
        return $Request->cookie[$key];
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
    public static function set($key,$val,$time=null) {
        $config = \x\Config::get('app');
        $key = $config['cookies_prefix'].$key;
        if (empty($time)) {
            $time = $config['cookies_outtime'];
        }
        $time += time();
        $Response = \x\context\Response::get();
        return $Response->cookie($key, $val, $time, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
    }

    /**
     * 删除
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param string $key
     * @return bool
    */
    public static function delete($key) {
        $config = \x\Config::get('app');
        $key = $config['cookies_prefix'].$key;
              
        $Request = \x\context\Request::get();
        $Response = \x\context\Response::get();
        if (isset($Request->cookie[$key]) == false) return false;
        
        return $Response->cookie($key, null, -1, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
    }

    /**
     * 清空
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return bool
    */
    public static function clear() {
        $config = \x\Config::get('app');
        $Request = \x\context\Request::get();
        $Response = \x\context\Response::get();

        if (isset($Request->cookie) == false) return false;

        foreach ($Request->cookie as $key=>$val) {
            $Response->cookie($key, null, -1, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }
        
        return true;
    }
    
}