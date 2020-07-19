<?php
// +----------------------------------------------------------------------
// | Param注解解析类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\doc\lable;
use \x\doc\lable\Basics;

class Param extends Basics
{
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.18
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @return true
    */
    public function run($route){
        # 检测路由类型
        $is_get = false;
        $is_post = false;
        if (isset($route['method'])) {
            $http_type = explode('|', strtoupper($route['method']));
            $status = false;
            foreach ($http_type as $v) {
                if ($v == $this->request->server['request_method']) {
                    $status = true;
                }
                if ($v == 'GET') $is_get = true;
                if ($v == 'POST') $is_post = true;
            }
            
            if ($status == false) {
                return $this->route_error('Route Method');
            }
        }

        # 注解参数检测
        if (isset($route['own']['Param'])) {
            if ($is_get) $get_list = $this->request->get;
            if ($is_post) $post_list = $this->request->post;

            foreach ($route['own']['Param'] as $val) {
                if (empty($val['name'])) continue;
                $name = $val['name'];
                // 获取回调事件名称
                $callback = '';
                if (!empty($val['callback'])) {
                    $callback = $val['callback'];
                }

                // 提示内容
                $tips = $val['tips']??'';

                // 先获取参数
                $param = '';
                if ($is_get) $param = $get_list[$name]??'';
                if (empty($param)) {
                    if ($is_post) $param = $post_list[$name]??'';
                }
                
                // 参数预设
                if (isset($val['value']) && empty($param) && $param != '0') {
                    $param = $val['value'];
                    if ($is_get) $this->request->get[$name] = $val['value'];
                    if ($is_post) $this->request->post[$name] = $val['value'];
                }

                // 判断是否允许为空
                $null = false;
                if (isset($val['empty']) && $val['empty'] == 'true') {
                    if (!isset($param)) {
                        $null = true;
                    } else if (trim($param) == '') {
                        $null = true;
                    }
                }
                // 不允许为空
                if ($null) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'NULL');
                }

                // 类型判断
                if (!empty($val['type']) && !empty($param)) {
                    $param_type = explode('|', $val['type']);
                    $param_status = false;
                    $attach = '';
                    foreach ($param_type as $v) {
                        $is = 'is_'.$v;
                        if ($is($param)) {
                            $param_status = true;
                        } else {
                            $attach .= $is.'、';
                        }
                    }
                    // 全都没通过
                    if ($param_status === false) {
                        // 中断
                        return $this->param_error_callback($callback, $tips, $name, 'TYPE', rtrim($attach, '、'));
                    }
                }

                // 长度判断
                $chinese = false;
                if (!empty($val['chinese']) && $val['chinese'] == 'true') {
                    $chinese = true;
                }
                if ($chinese) {
                    $length = mb_strlen($param, 'UTF8'); 
                } else {
                    $length = strlen($param); 
                }
                // 最小长度判断
                if (!empty($val['min']) && $val['min'] > $length) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'MIN');
                }
                // 最大长度判断
                if (!empty($val['max']) && $val['max'] < $length) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'MAX');
                }
                // 正则判断regular
                if (!empty($val['regular']) && !preg_match($val['regular'], $param)) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'REGULAR', $val['regular']);
                }
            }
        }

        // 更新容器
        return $this->_return();
    }

}