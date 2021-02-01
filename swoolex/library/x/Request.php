<?php
// +----------------------------------------------------------------------
// | 请求类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Request
{
    /**
     * 获取请求信息
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public static function request() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        return $Request;
    }

    /**
     * 获取请求头
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public static function header() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        return $Request->header;
    }

    /**
     * 获取raw参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function raw() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        return $Request->rawContent();
    }

    /**
     * 获取get参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function get() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        return $Request->get;
    }

    /**
     * 获取post参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function post() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        return $Request->post;
    }

    /**
     * 判断是否GET请求
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function is_get() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        if ($Request->server['request_method'] == 'GET') return true;
        return false;
    }

    /**
     * 判断是否POST请求
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function is_post() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        if ($Request->server['request_method'] == 'POST') return true;
        return false;
    }

    /**
     * 判断是否AJAX请求
     * @todo 无
     * @author 小黄牛
     * @version v1.2.19 + 2020.12.10
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function is_ajax() {
        $Request = \x\Container::getInstance()->get('request');
        if (!$Request) return false;
        if (isset($Request->header['x-requested-with']) && $Request->header['x-requested-with'] == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    /**
     * 是否使用SSL
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public static function is_ssl() {
        if (\x\Config::run()->get('server.ssl_cert_file') && \x\Config::run()->get('server.ssl_key_file')) return true;
        return false;
    }

    /**
     * 获取客户端真实IP
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @return string|false
    */
    public static function ip() {
        $Request = \x\Container::getInstance()->get('request');

        if (!empty($Request->header['x-real-ip'])) {
            return $Request->header['x-real-ip'];
        }
        if (!empty($Request->server['remote_addr'])) {
            return $Request->server['remote_addr'];
        }
        return false;
    }

    /**
     * 获取当前域名
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function domain() {
        $ret = 'http';
        if (self::is_ssl()) {
            $ret = 'https';
        }
        $Request = \x\Container::getInstance()->get('request');
        return $ret.'://'.$Request->header['host'];
    }

    /**
     * 获取当前请求路由
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function route() {
        $Request = \x\Container::getInstance()->get('request');
        if (!empty($Request->server['path_info'])) {
            return $Request->server['path_info'];
        }
        if (!empty($Request->server['request_uri'])) {
            return $Request->server['request_uri'];
        }
        return false;
    }

    /**
     * 获取完整URL
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否带域名
     * @return string|false
    */
    public static function url($status=false) {
        $ret = '';
        if ($status) {
            $ret = self::domain();
        }
        return $ret.self::route();
    }
    
    /**
     * 获取完整URL，带get参数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否带域名
     * @return string|false
    */
    public static function baseUrl($status=false) {
        $ret = '';
        if ($status) {
            $ret = self::domain();
        }
        $ret .= self::route();
        
        $Request = \x\Container::getInstance()->get('request');
        if (!empty($Request->server['query_string'])) {
            $ret .= '?'.$Request->server['query_string'];
        }
        
        return $ret;
    }


}