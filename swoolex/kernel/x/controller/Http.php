<?php
/**
 * +----------------------------------------------------------------------
 * HTTP服务 - 控制器基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\controller;

class Http {
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
     * 模板变量
    */
    private $assign = [];

    /**
     * 利用析构函数，自动回收归还连接池
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
    */
    public function __destruct() {
        $list = get_object_vars($this);
        foreach ($list as $name=>$value) {
            if (is_object($value)) {
                $obj = get_class($value);
                if ($obj == 'x\\Db' || $obj == 'x\\Redis') {
                    $this->$name->return();
                }
            }
        }
    }

    /**
     * 输出内容到页面
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $string 输出内容
     * @param int $status 响应状态码
     * @param string|array 响应头
     * @return int
    */
    public final function fetch($string, $status=200, $headers=[]) {
        // 清空模板变量，防止大意的开发者
        $this->assign = [];
        if (!empty($this->view)) $this->view->delete_assign();

        $Response = \x\context\Response::get();
        if (!$Response) return false;

        $Response->status($status);

        if (empty($headers)) {
            $headers = \x\Config::get('view.http_response_headers');
        }
        foreach ($headers as $key=>$value) {
            $Response->header($key, $value);
        }
        $status = $Response->end($string);

        return $status;
    }

    /**
     * 模板变量赋值
     * @author 小黄牛
     * @version v1.2.6 + 2020.07.22
     * @param  mixed $name  要显示的模板变量
     * @param  mixed $value 变量的值
     * @return $this
    */
    public final function assign($name, $value = '')
    {
        $this->assign[$name] = $value;
        return $this;
    }

    /**
     * 加载模板输出
     * @author 小黄牛
     * @version v1.2.6 + 2020.07.22
     * @param  string $template 模板文件名
     * @param  array  $vars     模板输出变量
     * @param  array  $config   模板参数
     * @return mixed
    */
    public final function view($template = '', $vars = [], $config = [])
    {
        $this->is_view();
        foreach ($this->assign as $name=>$value) {
            $this->view->assign($name, $value);
        }
        $content = $this->view->fetch($template, $vars, $config);
        return $content;
    }

    /**
     * 渲染内容输出
     * @author 小黄牛
     * @version v1.2.6 + 2020.07.22
     * @param  string $content 模板内容
     * @param  array  $vars    模板输出变量
     * @param  array  $config  模板参数
     * @return mixed
    */
    public final function display($content = '', $vars = [], $config = [])
    {
        $this->is_view();
        foreach ($this->assign as $name=>$value) {
            $this->view->assign($name, $value);
        }
        $content = $this->view->display($content, $vars, $config);
        // 清空模板变量，防止大意的开发者
        $Response = \x\context\Response::get();
        
        // 清空模板变量，防止大意的开发者
        unset($vars);
        $this->assign = [];
        if (!empty($this->view)) $this->view->delete_assign();

        $DebugGer = new \x\DebugGer();
        $debug_html = $DebugGer->run();
        return $Response->end($content.$debug_html);
    }

    /**
     * 视图过滤
     * @author 小黄牛
     * @version v1.2.6 + 2020.07.22
     * @param  Callable $filter 过滤方法或闭包
     * @return $this
    */
    public final function filter($filter)
    {
        $this->is_view();
        $this->view->filter($filter);
        return $this;
    }

    /**
     * 模板布局开关
     * @author 小黄牛
     * @version v1.2.6 + 2020.07.22
     * @param mixed $mixed
     * @return this
    */
    public final function layout($mixed) {
        $this->is_view();
        $this->view->engine->layout($mixed);
        return $this;
    }

    /**
     * 重定向
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $url 重定向地址
     * @param int $status 响应状态码
     * @param array $data 跳转时需要带上的get参数
    */
    public final function redirect($url, $status=302, $data=[]) {
        $url = $this->get_url($url, $data);
        $Response = \x\context\Response::get();
        return $Response->redirect($url, $status);
    }

    /**
     * 文件上传1-注入表单
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param string|array $name 表单名称，或者FILE
     * @return this
    */
    public final function file($name=null) {
        $this->file = $name;
        return $this;
    }

    /**
     * 文件上传2-注入配置
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param array $config
     * @return this
    */
    public final function validate($config=[]) {
        $this->file_config = $config;
        return $this;
    }

    /**
     * 文件上传3-保存
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @param string $path 保存路径
     * @param string $name 自定义保存名称
     * @return this
    */
    public final function move($path=null, $name=null) {
        $config = \x\Config::get('app.file');
        $config = array_merge($config, $this->file_config);

        if ($path) {
            $config['path'] = $path;
        }

        $file_data = $this->file;
        if (is_array($this->file) == false) {
            $Request = \x\context\Request::get();
            if (isset($Request->files[$this->file]) == false) {
                $this->file_error = 'Form does not exist';
                return false;
            }
            $file_data = $Request->files[$this->file];
        }

        if ($file_data['size'] > $config['size']) {
            $this->file_error = 'Upload exceeds maximum limit';
            return false;
        }

        $suffix_array = explode(',', str_replace(['.', ' '], '', $config['ext']));
        $arr = explode('.', $file_data['name']);
        $suffix = end($arr);
        
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
            $config['path'] .= '/';
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
        $this->file_path = $config['path'].$name;
        return $this;
    }

    /**
     * 文件上传4-获取错误日志
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return string
    */
    public final function getError() {
        return $this->file_error;
    }

    /**
     * 文件上传4-获取上传的文件名称
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return string
    */
    public final function getFileName() {
        return $this->file_name;
    }

    /**
     * 文件上传4-获取上传的文件完整路径
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return string
    */
    public final function getSaveName() {
        return $this->file_path;
    }

    /**
     * 图形验证码
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
	 * @param int $num 验证码使用模式 默认为英数混合 1英数混合 2数字运算
	 * @param string $session 验证码的seesion名
	 * @param array $type 验证码属性
	 * @return bool
	*/
	public final function verify($num=1, $session=null, $type=null) {
        $Response = \x\context\Response::get();
        \x\verify\Figure::entry($num, $session, $type, $Response);
    }

    /**
     * 图形验证码校验
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
	 * @param string $code 用户验证码
	 * @param string $session 验证码保存的seesion名
	 * @param bool
	*/
	public final function verify_check($code, $session=null) {
        return \x\verify\Figure::check($code, $session);
    }

    /**
     * 构造出跳转地址
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param string $url
     * @param array $data
     * @return string
    */
    private function get_url($url, $data) {
        if (strpos($url, '//') === false) {
            $url = '/'.ltrim($url, '/');
            if ($url != '/') {
                $url .= \x\Config::get('route.suffix');
            }
            $url = \x\Request::domain().$url;
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
            $this->view = new \x\View(\x\Config::get('view'));
        }
    }
}