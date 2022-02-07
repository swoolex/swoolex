<?php
/**
 * +----------------------------------------------------------------------
 * HTTP客户端
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\client;

class Http
{
    /**
     * 域名
    */
    private $domain;
    /**
     * API地址
    */
    private $api_url;
    /**
     * 端口
    */
    private $port = 80;
    /**
     * 请求内容
    */
    private $data = null;
    /**
     * 客户端实例
    */
    private $client_class;
    private $client;
    /**
     * 以下为client返回的信息
    */
    private $errCode;
    private $statusCode;
    private $body;
    private $headers;
    private $cookies;

    /**
     *  发送请求 - get
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function get() {
        // 设置请求参数
        if ($this->data) {
            $this->api_url .= '?'.http_build_query($this->data);
        }
        // 发送请求
        $this->client->get($this->api_url);

        $this->errCode = $this->client->errCode;
        $this->statusCode = $this->client->statusCode;
        $this->body = $this->client->body;
        $this->headers = $this->client->getHeaders();
        $this->cookies = $this->client->getCookies();

        $this->client->close();
        return $this->body;
    }
    /**
     * 发送请求 - post
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function post() {
        // 发送请求
        $this->client->post($this->api_url, $this->data);

        $this->errCode = $this->client->errCode;
        $this->statusCode = $this->client->statusCode;
        $this->body = $this->client->body;
        $this->headers = $this->client->getHeaders();
        $this->cookies = $this->client->getCookies();

        $this->client->close();
        return $this->body;
    }
    /**
     * 发送请求 - 下载文件
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @param string $filename 文件保存路径
     * @param mixed $offset 文件存在是否直接覆盖
     * @return void
    */
    public function download($filename, $offset=0) {
         // 发送请求
         $this->client->download($this->api_url, $filename, $offset);

         $this->errCode = $this->client->errCode;
         $this->statusCode = $this->client->statusCode;
         $this->body = $this->client->body;
         $this->headers = $this->client->getHeaders();
         $this->cookies = $this->client->getCookies();
 
         $this->client->close();
         return $this->body;
    }
    /**
     * 注入请求地址
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @param string $url API地址
     * @return this
    */
    public function domain($url) {
        $array = parse_url($url);
        // 端口
        if (isset($array['scheme'])) {
            $agreement = strtolower($array['scheme']);
            if ($agreement == 'http') {
                $this->port = 80;
            } else if ($agreement == 'https') {
                $this->port = 443;
            }
        }
        // 域名
        if (isset($array['host'])) {
            $this->domain = $array['host'];
        }
        // 端口
        if (isset($array['port'])) {
            $this->port = $array['port'];
        }
        // API地址
        if (isset($array['path'])) {
            $this->api_url = $array['path'];
        }
        if (isset($array['query'])) {
            $this->api_url .= '?'.$array['query'];
        }

        // 创建客户端实例
        $this->client_class = new \ReflectionClass('\Swoole\Coroutine\Http\Client');
        if ($this->port == 443) {
            $args = [$this->domain, $this->port, true];
        } else {
            $args = [$this->domain, $this->port];
        }
        $this->client = $this->client_class->newInstanceArgs($args);
        return $this;
    }
    /**
     * 注入请求内容
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $data 请求内容
     * @return this
    */
    public function body($data=null) {
        $this->data = $data;
        return $this;
    }
    /**
     * 获取errCode
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function errCode(){ return $this->errCode;}
    /**
     * 获取statusCode
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function statusCode(){ return $this->statusCode;}
    /**
     * 获取headers
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function headers(){ return $this->headers;}
    /**
     * 获取cookies
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function cookies(){ return $this->cookies;}
    /**
     * 获取响应头
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function getHeaders(){ return $this->client->getHeaders();}
    /**
     * 反射类实现调用Client本身的其他支持方法
     * @todo 无
     * @author 小黄牛
     * @version v1.2.11 + 2020.08.06
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __call($name, $arguments=[]) {
        if (!$this->client) return false;
        if (empty($name)) return false;
        if (!$this->client_class->hasMethod($name)) return false;

        $obj = $this->client_class->getmethod($name);
        $obj->invokeArgs($this->client, $arguments);
        return $this;
    }
}