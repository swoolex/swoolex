<?php
/**
 * +----------------------------------------------------------------------
 * 模板引擎规则
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/top-think/think-view
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\view\driver;

class SwooleX {
    /**
     * 模板引擎实例
    */
    private $template;

    /**
     * 初始化
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param array $config 视图配置
    */
    public function __construct($config) {
        $this->template = new \x\Template($config);
    }

    /**
     * 检测是否存在模板文件
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $template 模板文件或者模板规则
     * @return bool
    */
    public function exists($template) {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        return is_file($template);
    }

    /**
     * 渲染模板文件
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $template 模板文件
     * @param array $data 模板变量
     * @param array $config 模板参数
    */
    public function fetch($template, $data = [], $config = []) {
        if ('' == pathinfo($template, PATHINFO_EXTENSION)) {
            // 获取模板文件名
            $template = $this->parseTemplate($template);
        }

        $this->template->fetch($template, $data, $config);
    }

    /**
     * 渲染模板内容
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $template 模板内容
     * @param array $data 模板变量
     * @param array $config 模板参数
    */
    public function display($template, $data = [], $config = []) {
        $this->template->display($template, $data, $config);
    }

    /**
     * 自动定位模板文件
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $template 模板文件规则
     * @return string
    */
    private function parseTemplate($template) {
        return $template;
    }

    /**
     * 配置或者获取模板引擎参数
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string|array $name 参数名
     * @param mixed $value 参数值
     * @return mixed
    */
    public function config($name, $value = null) {
        if (is_array($name)) {
            $this->template->config($name);
            $this->config = array_merge($this->config, $name);
        } elseif (is_null($value)) {
            return $this->template->config($name);
        } else {
            $this->template->$name = $value;
            $this->config[$name]   = $value;
        }
    }

    /**
     * 当方法不存在时，指向模板解析类本身
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @return object
    */
    public function __call($method, $params) {
        return call_user_func_array([$this->template, $method], $params);
    }
}