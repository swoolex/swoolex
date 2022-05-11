<?php
/**
 * +----------------------------------------------------------------------
 * 请求类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Request
{
    /**
     * 获取请求信息
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @return array
    */
    public static function request() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return $Request;
    }

    /**
     * 获取请求头
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @return array
    */
    public static function header() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return $Request->header;
    }

    /**
     * 获取raw数据流
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return array
    */
    public static function raw() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return $Request->rawContent();
    }

    /**
     * 获取get参数
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @param array $field 需要返回参数的key名，一维
     * @return array
    */
    public static function get($field=[]) {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return self::return_field($Request->get, $field);
    }

    /**
     * 获取post参数
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @param array $field 需要返回参数的key名，一维
     * @return array
    */
    public static function post($field=[]) {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return self::return_field($Request->post, $field);
    }

    /**
     * 获取get|post参数
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @param array $field 需要返回参数的key名，一维
     * @return array
    */
    public static function param($field=[]) {
        $Request = \x\context\Request::get();
        if (!$Request) return false;

        if (!empty($Request->post)) return self::return_field($Request->post, $field);
        return self::return_field($Request->get, $field);
    }

    /**
     * 获取files参数
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @param array $field 需要返回参数的key名，一维
     * @return array
    */
    public static function file($field=[]) {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        return self::return_field($Request->files, $field);
    }
    
    /**
     * 判断是否GET请求
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return bool
    */
    public static function is_get() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        if ($Request->server['request_method'] == 'GET') return true;
        return false;
    }

    /**
     * 判断是否POST请求
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return bool
    */
    public static function is_post() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        if ($Request->server['request_method'] == 'POST') return true;
        return false;
    }

    /**
     * 判断是否AJAX请求
     * @author 小黄牛
     * @version v1.2.19 + 2020.12.10
     * @return bool
    */
    public static function is_ajax() {
        $Request = \x\context\Request::get();
        if (!$Request) return false;
        if (isset($Request->header['x-requested-with']) && $Request->header['x-requested-with'] == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    /**
     * 是否使用SSL
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @return bool
    */
    public static function is_ssl() {
        if (\x\Config::get('server.ssl_cert_file') && \x\Config::get('server.ssl_key_file')) return true;
        return false;
    }

    /**
     * 获取客户端真实IP
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @return string|false
    */
    public static function ip() {
        $Request = \x\context\Request::get();

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
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @return string
    */
    public static function domain() {
        $ret = 'http';
        if (self::is_ssl()) {
            $ret = 'https';
        }
        $Request = \x\context\Request::get();
        return $ret.'://'.$Request->header['host'];
    }

    /**
     * 获取当前请求路由
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @return string
    */
    public static function route() {
        $Request = \x\context\Request::get();
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
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
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
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @param bool $status 是否带域名
     * @return string|false
    */
    public static function baseUrl($status=false) {
        $ret = '';
        if ($status) {
            $ret = self::domain();
        }
        $ret .= self::route();
        
        $Request = \x\context\Request::get();
        if ($status == true && !empty($Request->server['query_string'])) {
            $ret .= '?'.$Request->server['query_string'];
        }
        
        return $ret;
    }

    /**
     * 返回值格式化
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @param array $param
     * @param array $field
     * @return false|array
    */
    private static function return_field($param, $field) {
        if (!$param) return false;
        if (empty($field)) return $param;

        $list = [];
        foreach ($field as $key) {
            if (isset($param[$key])) $list[$key] = $param[$key];
        }

        return $list;
    }

}