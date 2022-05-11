<?php
/**
 * +----------------------------------------------------------------------
 * Jwt注解解析类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route\doc\lable;
use \x\route\doc\lable\Basics;

class Jwt extends Basics
{
    /**
     * 启动项
     * @author 小黄牛
     * @version v1.2.10 + 2020.07.30
     * @param array $route 路由参数
     * @return true
    */
    public function run($route){
        if (!isset($route['own']['Jwt'])) {
            // 更新容器
            return $this->_return();
        }
        $route = $route['own']['Jwt'];

        // 错误提示内容
        $tips = '';
        if (!empty($route['tips'])) $tips = $route['tips'];
        // 类型
        $type = 'HEADER';
        if (!empty($route['type'])) $type = strtoupper($route['type']);
        switch ($type) {
            case 'GET':
                $param = $this->request->get;
            break;
            case 'POST':
                $param = $this->request->post;
            break;
            case 'RAW':
                $param = $this->request->rawContent();
            break;
            case 'HEAD':
                $param = $this->request->header();
            break;
            default:
                return $this->jwt_error(!empty($tips) ? $tips : 'Type Error');
            break;
        }

        // 表单名
        $name = \x\Config::get('jwt.jwt_form_name');
        if (!empty($route['name'])) $name = $route['name'];
        if (empty($param[$name])) return $this->jwt_error(!empty($tips) ? $tips : $name.' is Empty');

        // 校验jwt_Token
        $Jwt = new \x\Jwt();
        if (!$Jwt->is_token($param[$name])) return $this->jwt_error(!empty($tips) ? $tips : 'Jwt Token Error');


        // 更新容器
        return $this->_return();
    }

    /**
     * 注解检测失败时，回调的处理函数
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @param string $status 错误事件状态码
     * @return bool
    */
    protected function jwt_error($status) {
        // 若为单元测试调试，则直接通过
        if (
            (!empty($this->request->get['SwooleXTestCase'])) || 
            (!empty($this->request->post['SwooleXTestCase']))
        ) {
            return true;
        }
        return \design\Lifecycle::jwt_error($status);
    }
}