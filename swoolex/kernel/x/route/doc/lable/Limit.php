<?php
/**
 * +----------------------------------------------------------------------
 * Limit注解解析类
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

class Limit extends Basics
{
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
        if (!isset($route['own']['Limit'])) {
            // 更新容器
            return $this->_return();
        }
        $route = $route['own']['Limit'];

        // 这里可能只是用于更新限流器配置信息
        // 并在限流器中标记该条信息已被注解修改过，不允许重复修改，防止请求并发

        // 更新容器
        return $this->_return();
    }

}