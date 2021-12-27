<?php
/**
 * +----------------------------------------------------------------------
 * HTTP服务 - Ajax请求类型过滤
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

class HttpAjax extends Basics {
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.10 + 2020.07.30
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @return true
    */
    public function run($route){
        if (isset($route['own']['Ajax'])) {
            if (isset($this->request->header['x-requested-with']) == false || $this->request->header['x-requested-with'] != 'XMLHttpRequest') {
                $msg = array_shift($route['own']['Ajax']);
                if (!$msg) $msg = 'Route Method Ajax';

                return $this->route_error($msg);
            }
        }

        // 更新容器
        return $this->_return();
    }
}