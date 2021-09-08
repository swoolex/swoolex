<?php
/**
 * +----------------------------------------------------------------------
 * 注解标签基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route\doc\lable;

class Basics
{
    /**
     * 初始化请求
    */
    protected $server;
    protected $fd;
    protected $request;
    protected $response;
    protected $websocket_server;
    protected $websocket_frame;
    protected $controller_instance;
    protected $controller_method;

    public function __construct($server=null, $fd=null) {
        // 获取容器
        $this->server = $server;
        $this->fd = $fd;
        $this->request = \x\context\Request::get();
        $this->response = \x\context\Response::get();
        $this->websocket_server = \x\context\Container::get('websocket_server');
        $this->websocket_frame = \x\context\Container::get('websocket_frame');
        $this->controller_instance = \x\context\Container::get('controller_instance');
        $this->controller_method = \x\context\Container::get('controller_method');
    }

    // 需要主动更新容器
    protected function _return($return=true) {
        \x\context\Request::set($this->request);
        \x\context\Response::set($this->response);
        \x\context\Container::set('websocket_server', $this->websocket_server);
        \x\context\Container::set('websocket_frame', $this->websocket_frame);
        \x\context\Container::set('controller_instance', $this->controller_instance);
        \x\context\Container::set('controller_method', $this->controller_method);
        return $return;
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
		
		\design\Lifecycle::annotate_param($this->server, $this->fd, $callback, $tips, $name, $status, $attach);
        
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
		
        \design\Lifecycle::route_error($this->server, $this->fd, $status);
        
        return false;
    }
}