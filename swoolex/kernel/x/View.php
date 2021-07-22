<?php
/**
 * +----------------------------------------------------------------------
 * 模板引擎核心类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class View {
    /**
     * 模板引擎实例
     */
    public $powerObj;
    /**
     * 模板变量
     */
    protected $data = [];
    /**
     * 全局模板变量
     */
    protected static $var = [];

    /**
     * 初始化
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $config 配置参数
     * @return void
    */
    public function __construct($config) {
        // 初始化模板引擎
        $this->power($config['type'], $config);
        return $this;
    }

    /**
     * 模板变量静态赋值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $name 变量名
     * @param mixed $value 变量值
     * @return $this
    */
    public function share($name, $value = '') {
        if (is_array($name)) {
            self::$var = array_merge(self::$var, $name);
        } else {
            self::$var[$name] = $value;
        }

        return $this;
    }

    /**
     * 清理模板变量
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function clear() {
        self::$var  = [];
        $this->data = [];
    }

    /**
     * 模板变量赋值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $name变量名
     * @param mixed $value 变量值
     * @return $this
    */
    public function assign($name, $value = '') {
        if (is_array($name)) {
            $this->data = array_merge($this->data, $name);
        } else {
            $this->data[$name] = $value;
        }

        return $this;
    }

    /**
     * 模板变量清空
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function delete_assign() {
        $this->data = [];
    }

    /**
     * 设置当前模板解析的引擎
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array|string $options 引擎参数
     * @return $this
    */
    public function power($type, $config) {
        $class = "\x\\view\driver\\".$type;
        $this->powerObj = new $class($config);
        return $this;
    }

    /**
     * 配置模板引擎
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $name 参数名
     * @param mixed $value 参数值
     * @return $this
    */
    public function config($name, $value = null)
    {
        $this->powerObj->config($name, $value);

        return $this;
    }

    /**
     * 检查模板是否存在
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $name 参数名
     * @return bool
    */
    public function exists($name) {
        return $this->powerObj->exists($name);
    }

    /**
     * 解析和获取模板内容 用于输出
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string $template 模板文件名或者内容
     * @param array $vars 模板输出变量
     * @param array $config 模板参数
     * @param bool $renderContent 是否渲染内容
     * @return string
    */
    public function fetch($template = '', $vars = [], $config = [], $renderContent = false) {
        // 模板变量
        $vars = array_merge(self::$var, $this->data, $vars);
        $this->data = [];
        // 页面缓存
        ob_start();
        ob_implicit_flush(0);

        // 渲染输出
        try {
            $method = $renderContent ? 'display' : 'fetch';
            $this->powerObj->$method($template, $vars, $config);
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        // 获取并清空缓存
        $content = ob_get_clean();

        return $content;
    }

    /**
     * 渲染内容输出
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string $content 内容
     * @param array $vars 模板输出变量
     * @param array $config 模板参数
     * @return void
    */
    public function display($content, $vars = [], $config = []) {
        return $this->fetch($content, $vars, $config, true);
    }

    /**
     * 模板变量赋值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string $name变量名
     * @param mixed $value 变量值
     * @return void
    */
    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    /**
     * 取得模板显示变量的值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string $name 模板变量
     * @return void
    */
    public function __get($name) {
        return $this->data[$name];
    }

    /**
     * 检测模板变量是否设置
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param string $name 模板变量名
     * @return void
    */
    public function __isset($name) {
        return isset($this->data[$name]);
    }
}
