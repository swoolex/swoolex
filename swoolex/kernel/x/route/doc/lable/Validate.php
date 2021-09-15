<?php
/**
 * +----------------------------------------------------------------------
 * Validate注解解析类
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

class Validate extends Basics {
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021.09.15
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @param string $server_type 服务类型
     * @return true
    */
    public function run($route, $server_type){
        # 注解参数检测
        if (!isset($route['own']['Validate'])) return $this->_return();

        switch ($server_type) {
            case 'http':
                $data = \x\Request::param();
            break;
            case 'websocket':
                $obj = new \x\controller\WebSocket();
                $data = $obj->get_data();
            break;
            case 'rpc':
                $data = $this->controller_instance->param;
            break;
            case 'mqtt':
                $data = $this->controller_instance->getData();
            break;
            default:
                return $this->_return();
            break;
        }
            
        foreach ($route['own']['Validate'] as $val) {
            $class = !empty($val['class']) ? $val['class'] : '/x/Validate';
            $class = str_replace('/', '\\', $class);
            if (!empty($val['scene']) && ($val['scene'] =='true' || $val['scene'] == '1')) {
                $scene = true;
            } else {
                $scene = false;
            }
            if (!empty($val['batch']) && ($val['batch'] =='true' || $val['batch'] == '1')) {
                $batch = true;
            } else {
                $batch = false;
            }
            $filter = !empty($val['filter']) ? $val['filter'] : '';
            $field = !empty($val['field']) ? $val['field'] : '';
            $callback = !empty($val['callback']) ? $val['callback'] : '\box\lifecycle\validate_error';

            $Validate = new $class();
            if ($Validate->scene($scene)->batch($batch)->filter($filter)->addfield($filter)->fails($data)) {
                return $this->validate_error($server_type, $batch, $Validate->errors(), $callback);
            }
        }
        // 更新容器
        return $this->_return();
    }

    /**
     * 注解检测失败时，回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $server_type
     * @param bool $batch
     * @param array $errors
     * @return void
    */
    protected function validate_error($server_type, $batch, $errors, $callback) {
        return \design\Lifecycle::validate_error($server_type, $batch, $errors, $callback);
    }
}