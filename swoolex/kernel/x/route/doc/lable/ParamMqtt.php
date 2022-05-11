<?php
/**
 * +----------------------------------------------------------------------
 * Mqtt服务 - Param注解解析类
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

class ParamMqtt extends Basics {

    /**
     * 启动项
     * @author 小黄牛
     * @version v1.2.10 + 2020.07.30
     * @param array $route 路由参数
     * @return true
    */
    public function run($route){
        # 注解参数检测
        if (isset($route['own']['Param'])) {
            $mqtt_list = $this->controller_instance->getData();

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
                $param = $mqtt_list[$name]??'';
                    
                // 参数预设
                if (isset($val['value'])) {
                    if (!isset($mqtt_list[$name])) {
                        $param = $val['value'];
                        $mqtt_list[$name] = $val['value'];
                    }
                }
                // 判断是否允许为空
                $null = false;
                if (isset($val['empty']) && $val['empty'] == 'true') {
                    if (!isset($param)) {
                        $null = true;
                    } else if (is_array($param) == false && trim($param) == '') {
                        $null = true;
                    }
                }

                // 不允许为空
                if ($null) {
                    // 中断
                    return $this->param_error_callback($callback, $tips, $name, 'NULL');
                }
                // 只有真的不为空，才走这个规则
                if (is_array($param) == false && $this->isset_empty($param) == true) {
                    // 验证器
                    if (!empty($val['validate'])) {
                        $alias = !empty($val['alias']) ? $val['alias'] : null;
                        $Validate = new \x\Validate();
                        if ($Validate->field($name)->alias($alias)->rule($val['validate'])->fails([$name => $param])) {
                            $error = $Validate->errors()[0];
                            // 中断
                            return $this->param_error_callback($callback, $error['message'], $name, 'VALIDATE', $error['rule']);
                        }
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

            $this->controller_instance->setData($mqtt_list);
        }

        // 更新容器
        return $this->_return();
    }

    /**
     * 判断参数是否真null
     * 如果是真的null会返回false
     * @author 小黄牛
     * @version v1.0.1 + 2020.07.24
     * @param string $param
     * @return bool
    */
    private function isset_empty($param) {
        if (!isset($param)) return false;
        if (trim($param) == '') return false;
        return true;
    }
}