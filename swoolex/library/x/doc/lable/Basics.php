<?php
// +----------------------------------------------------------------------
// | 注解标签基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\doc\lable;

class Basics
{
    /**
     * 初始化请求
    */
    protected $request;
    protected $response;
    protected $websocket_server;
    protected $websocket_frame;
    protected $controller_instance;
    protected $controller_method;

    public function __construct() {
        // 获取容器
        $this->request = \x\Container::get('request');
        $this->response = \x\Container::get('response');
        $this->websocket_server = \x\Container::get('websocket_server');
        $this->websocket_frame = \x\Container::get('websocket_frame');
        $this->controller_instance = \x\Container::get('controller_instance');
        $this->controller_method = \x\Container::get('controller_method');
    }

    // 需要主动更新容器
    protected function _return() {
        \x\Container::set('request', $this->request);
        \x\Container::set('response', $this->response);
        \x\Container::set('websocket_server', $this->websocket_server);
        \x\Container::set('websocket_frame', $this->websocket_frame);
        \x\Container::set('controller_instance', $this->controller_instance);
        \x\Container::set('controller_method', $this->controller_method);
        return true;
    }

    /**
     * 当注解Param检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $callback 回调事件
     * @param string $tips 自定义提示内容
     * @param string $name 参数名称
     * @param string $status 错误事件状态码
     * @param string $attach 错误检测返回附加说明
     * @return void
    */
    protected function param_error_callback($callback, $tips, $name, $status, $attach=null) {
        // 若为单元测试调试，则直接通过
        if (
            (!empty($this->request->get['SwooleXTestCase'])) || 
            (!empty($this->request->post['SwooleXTestCase']))
        ) {
            return true;
        }
        // 如果不定义回调事件，则启用系统的生命周期回调处理
        if (empty($callback)) {
            $callback = '\other\lifecycle\\annotate_param';
        }
        // 判断回调事件是类，还是函数
        if (stripos($callback, '\\') !== false) {
            $obj = new $callback;
            $obj->run($tips, $name, $status, $attach);
        } else {
            $callback($tips, $name, $status, $attach);
        }
        return false;
    }

    /**
     * 当其余注解检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $status 错误事件状态码
     * @return void
    */
    protected function route_error($status) {
        // 若为单元测试调试，则直接通过
        if (
            (!empty($this->request->get['SwooleXTestCase'])) || 
            (!empty($this->request->post['SwooleXTestCase']))
        ) {
            return true;
        }
        
        $obj = new \other\lifecycle\route_error();
        $obj->run($status);
        return false;
    }
}