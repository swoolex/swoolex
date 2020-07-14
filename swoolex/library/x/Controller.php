<?php
// +----------------------------------------------------------------------
// | 控制器基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Controller
{
    /**
     * 请求 
    */
    private $setRequest;
    /**
     * 响应 
    */
    private $setResponse;
    /**
     * 表单
    */
    private $file;
    /**
     * 文件上传配置
    */
    private $file_config=[];
    /**
     * 文件上传错误提示
    */
    private $file_error;
    /**
     * 文件保存名称
    */
    private $file_name;
    /**
     * 文件保存地址
    */
    private $file_path;
    /**
     * 视图实例
     */
    private $view;

    /**
     * 注入请求
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param obj $setRequest
     * @return void
    */
    public final function setRequest($setRequest) {
        $this->setRequest = $setRequest;
    }

    /**
     * 注入响应
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param obj $setRequest
     * @return void
    */
    public final function setResponse($setResponse) {
        $this->setResponse = $setResponse;
    }

    /**
     * 输出内容到页面
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $string 输出内容
     * @param int $status 响应状态码
     * @return void
    */
    public final function fetch($string, $status=200) {
        try {
            $this->setResponse->status($status);
            return $this->setResponse->end($string);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 注入视图变量
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 变量名
     * @param mixed $val 变量内容
     * @return void
    */
    public function assign($key, $val=null){
        $this->is_view();
        $this->view->assign($key, $val);
    }

    /**
     * 输出视图
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $view 视图路径
     * @return void
    */
    public final function view($view=null) {
        if (!$view) {
            $array = explode(\x\Config::run()->get('route.suffix'), $this->setRequest->server['request_uri']);
            $view = ltrim($array[0], '/');
        }

        $this->is_view();
        # 调用视图类
        ob_start();
        $this->view->setRequest = $this->setRequest;
        $this->view->setResponse = $this->setResponse;
        $ret = $this->view->display($view);
        if ($ret) {
            try {
                $content = ob_get_clean();
                $this->setResponse->status(200);
                return $this->setResponse->end($content);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * 获取请求信息
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    public final function request() {
        return $this->setRequest;
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
    public final function header() {
        return $this->setRequest->header;
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
    public final function get() {
        return $this->setRequest->get;
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
    public final function post() {
        return $this->setRequest->post;
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
    public final function is_get() {
        $request = $this->request();
        if ($request->server['request_method'] == 'GET') return true;
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
    public final function is_post() {
        $request = $this->request();
        if ($request->server['request_method'] == 'POST') return true;
        return false;
    }

    /**
     * 重定向
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $url 重定向地址
     * @param int $status 响应状态码
     * @param array $data 跳转时需要带上的get参数
     * @return void
    */
    public final function redirect($url, $status=301, $data=[]) {
        $url = $this->get_url($url, $data);
        return $this->setResponse->redirect($url, $status);
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
    public final function is_ssl() {
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
    public final function ip() {
        if (!empty($this->setRequest->header['x-real-ip'])) {
            return $this->setRequest->header['x-real-ip'];
        }
        if (!empty($this->setRequest->server['remote_addr'])) {
            return $this->setRequest->server['remote_addr'];
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
    public final function domain() {
        $ret = 'http';
        if ($this->is_ssl()) {
            $ret = 'https';
        }
        return $ret.'://'.$this->setRequest->header['host'];
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
    public final function route() {
        if (!empty($this->setRequest->server['path_info'])) {
            return $this->setRequest->server['path_info'];
        }
        if (!empty($this->setRequest->server['request_uri'])) {
            return $this->setRequest->server['request_uri'];
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
    public final function url($status=false) {
        $ret = '';
        if ($status) {
            $ret = $this->domain();
        }
        return $ret.$this->route();
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
    public final function baseUrl($status=false) {
        $ret = '';
        if ($status) {
            $ret = $this->domain();
        }
        $ret .= $this->route();
        
        if (!empty($this->setRequest->server['query_string'])) {
            $ret .= '?'.$this->setRequest->server['query_string'];
        }
        
        return $ret;
    }

    /**
     * 文件上传1-注入表单
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $name 表单名称，或者FILE
     * @return void
    */
    public final function file($name=null) {
        $this->file = $name;
        return $this;
    }

    /**
     * 文件上传2-注入配置
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param array $config
     * @return void
    */
    public final function validate($config=[]) {
        $this->file_config = $config;
        return $this;
    }

    /**
     * 文件上传3-保存
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $path 保存路径
     * @param string $name 自定义保存名称
     * @return void
    */
    public final function move($path=null, $name=null) {
        $config = \x\Config::run()->get('app.file');
        $config = array_merge($config, $this->file_config);

        if ($path) {
            $config['path'] = $path;
        }

        $file_data = $this->file;
        if (is_array($this->file) == false) {
            if (isset($this->setRequest->files[$this->file]) == false) {
                $this->file_error = 'Form does not exist';
                return false;
            }
            $file_data = $this->setRequest->files[$this->file];
        }

        if ($file_data['size'] > $config['size']) {
            $this->file_error = 'Upload exceeds maximum limit';
            return false;
        }

        $suffix_array = explode(',', str_replace(['.', ' '], '', $config['type']));
        $suffix = str_replace([
            'image/',
            'application/',
            'video/',
            'audio/',
            'text/',
        ], '', $file_data['type']);

        if (in_array($suffix, $suffix_array) == false) {
            $this->file_error = 'Upload file type error';
            return false;
        }

        if (file_exists($config['path']) == false) {
            if ($config['auto_save'] == false) {
                $this->file_error = 'Save directory does not exist';
                return false;
            }

            $res = mkdir($config['path'], 0755, true); 
            if ($res === false) {
                $this->file_error = 'Save directory auto create failed';
                return false;
            }
        }
        
        $cutting = substr($config['path'], strlen($config['path'])-1, 1);
        if ($cutting != '/' && $cutting != '\\') {
            $config['path'].'/';
        }
        $config['path'] .= date('Ymd', time()).'/';

        if (file_exists($config['path']) == false) {
            if ($config['auto_save'] == false) {
                $this->file_error = 'Save directory does not exist';
                return false;
            }

            $res = mkdir($config['path'], 0755, true); 
            if ($res === false) {
                $this->file_error = 'Save directory auto create failed';
                return false;
            }
        }

        if (!$name) {
            if ($config['name_algorithm'] == 'time') {
                $name = time().'_'.rand();
            } else if ($config['name_algorithm'] == 'sha1') {
                $name = sha1(time().rand());
            } else if ($config['name_algorithm'] == 'md5') {
                $name = md5(time().rand());
            } else {
                $this->file_error = 'Unsupported filename generation';
                return false;
            }
            $name .= '.'.$suffix;
        }

        $res = move_uploaded_file($file_data['tmp_name'], $config['path'].$name);
        if (!$res) {
            $this->file_error = 'File save failed';
            return false;
        }

        $this->file_name = $name;
        $this->file_path = str_replace(ROOT_PATH, '', $name);
        return $this;
    }

    /**
     * 文件上传4-获取错误日志
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function getError() {
        return $this->file_error;
    }

    /**
     * 文件上传4-获取上传的文件名称
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function getFileName() {
        return $this->file_name;
    }

    /**
     * 文件上传4-获取上传的文件完整路径
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function getSaveName() {
        return $this->file_path;
    }

    /**
     * 图形验证码
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
	 * @param int $num 验证码使用模式 默认为英数混合 1英数混合 2数字运算
	 * @param string $session 验证码的seesion名
	 * @param array $type 验证码属性
	 * @param swool\response
	 * @return bool
	*/
	public final function verify($num=1, $session=null, $type=null) {
        \x\Verify::entry($num, $session, $type, $this->setResponse);
    }

    /**
     * 图形验证码校验
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
	 * @param string $code 用户验证码
	 * @param string $session 验证码保存的seesion名
	 * @param boool
	*/
	public final function verify_check($code, $session=null) {
        return \x\Verify::check($code, $session);
    }

    /**
     * 构造出跳转地址
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $url
     * @param array $data
     * @return string
    */
    private final function get_url($url, $data) {
        if (strpos($url, '//') === false) {
            $header = $this->header();
            $url = '//'.$header['host'].'/'.ltrim($url, '/').\x\Config::run()->get('route.suffix');
        }
        if (count($data)) {
            $url .= '?'.http_build_query($data);
        }
        return $url;
    }

    /**
     * 获得模板实例 
    */
    private function is_view(){
        if (empty($this->view)) {
            $this->view = new \x\View();
        }
    }
}