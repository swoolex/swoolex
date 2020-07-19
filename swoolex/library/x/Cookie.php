<?php
// +----------------------------------------------------------------------
// | Cookie类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Cookie
{
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
        $Request = \x\Container::getInstance()->get('request');
        $key = \x\Config::run()->get('app.cookies_prefix').$key;
        if (isset($Request->cookie[$key])) return true;
        
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
        $Request = \x\Container::getInstance()->get('request');
        $key = \x\Config::run()->get('app.cookies_prefix').$key;
        if (isset($Request->cookie[$key]) == false) return false;
        return $Request->cookie[$key];
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
    public static function set($key,$val,$time=null) {
        $config = \x\Config::run()->get('app');
        $key = $config['cookies_prefix'].$key;
        if (empty($time)) {
            $time = $config['cookies_outtime'];
        }
        $time += time();
        $Response = \x\Container::getInstance()->get('response');
        return $Response->cookie($key, $val, $time, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
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
        $config = \x\Config::run()->get('app');
        $key = $config['cookies_prefix'].$key;
              
        $Request = \x\Container::getInstance()->get('request');
        $Response = \x\Container::getInstance()->get('response');
        if (isset($Request->cookie[$key]) == false) return false;
        
        return $Response->cookie($key, null, -1, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
    }

    /**
     * 清空
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public static function clear() {
        $config = \x\Config::run()->get('app');
        $Request = \x\Container::getInstance()->get('request');
        $Response = \x\Container::getInstance()->get('response');

        if (isset($Request->cookie) == false) return false;

        foreach ($Request->cookie as $key=>$val) {
            $Response->cookie($key, null, -1, $config['cookies_path'], $config['cookies_domain'], $config['cookies_secure'], $config['cookies_httponly']);
        }
        
        return true;
    }
    
}