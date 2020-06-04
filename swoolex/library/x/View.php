<?php
// +----------------------------------------------------------------------
// | 视图层核心类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class View {
    /**
     * 模板变量
     */
    public $_view = [];
    /**
     * 请求 
    */
    public $setRequest;
    /**
     * 响应 
    */
    public $setResponse;

    /**
     * 模板变量注入方法
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $tpl_var 变量名
     * @param mixed $var 变量值
     * @return void
    */
	public function assign($tpl_var, $var = null) {
		if (isset($tpl_var) && !empty($tpl_var)) {
			$this->_view[$tpl_var] = $var;
		}
	}

    /**
     * 模板文件编译
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $file 模板文件路径 控制器/方法 OR 控制器/方法
     * @return void
    */
	public function display($file='') {
		# 模板文件地址
        $tpl_file  = ROOT_PATH.'/app/view/'.$file.\x\Config::run()->get('app.template.view_suffix');
		if (!file_exists($tpl_file)) {
            $this->error_404($tpl_file);
            return false;
		}

		# 编译文件存放地址
		$parse_file = ROOT_PATH.'/runtime/view/'.str_replace('/', '_', $file).'.php';

        # 只有当编译文件不存在 OR 模板文件被修改过 or 开启调试模式时
		# 编译文件才重新生成
		if (\x\Config::run()->get('app.de_bug') || !file_exists($parse_file) || filemtime($parse_file) < filemtime($tpl_file)) {
            # 执行编译解析
			$obj = new \x\view\Compile($tpl_file);
			$obj->parse($parse_file);
		}

        # 注入模板变量
        foreach ($this->_view as $SwooleXkey=>$SwooleXval) {
            $$SwooleXkey = $SwooleXval;
        }
		# 引入编译编译文件
        include $parse_file;
        return true;
    }
    
    /**
     * 404
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function error_404($file) {
        // 实例化基类控制器
        $controller = new \x\Controller();
        $controller->setRequest($this->setRequest);
        $controller->setResponse($this->setResponse);

        $class = \x\Config::run()->get('route.error_class');

        // 系统404
        if (\x\Config::run()->get('route.404') == false || empty($class)) {
            $controller->fetch('View：'.$file.' 404', '404');
        } else {
        // 自定义404
            new $class($controller);
        }
    }
}
