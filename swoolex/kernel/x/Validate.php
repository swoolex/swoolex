<?php
/**
 * +----------------------------------------------------------------------
 * 验证器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x;
use x\validate\Event;

class Validate
{
    /**
     * 全部验证一次
    */
    protected $batch = false;
    /**
     * 请求使用规则
    */
    protected $header_rule = [];
    /**
     * 字段使用规则
    */
    protected $rule = [];
    /**
     * 校验不通过时的错误声明
    */
    protected $message = [];
    /**
     * 设置验证数据
    */
    protected $data = [];
    /**
     * 设置message时的字段别名，会把{字段名}占位符替换
    */
    protected $alias = [];
    /**
     * 场景定义
    */
    protected $scene = [];
    /**
     * 选择使用哪个场景
    */
    protected $use_scene = null;
    /**
     * 需要移出验证的字段
    */
    protected $filter = [];
    /**
     * 需要验证的字段
    */
    protected $field = null;
    /**
     * 失败结果集
    */
    protected $errors = [];
    /**
     * 验证类型别名
     */
    protected $conversion = [
        '>' => 'gt', '>=' => 'egt', '<' => 'lt', '<=' => 'elt', '=' => 'eq', '!=' => 'neq',
    ];
    /**
     * 全局验证规则(不依赖参数)
    */
    protected $overall = ['expire'];

    /**
     * 设置batch
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param bool $bool
     * @return this
    */
    public final function batch($bool) {
        $this->batch = $bool;
        return $this;
    }
    /**
     * 设置field
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $field
     * @return this
    */
    public final function field($field) {
        $this->field = $field;
        return $this;
    }
    /**
     * 设置alias
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $alias
     * @return this
    */
    public final function alias($alias) {
        $this->alias = $alias;
        return $this;
    }
    /**
     * 设置rule
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param string|array $rule
     * @return this
    */
    public final function rule($rule) {
        $this->rule = $rule;
        return $this;
    }
    /**
     * 设置message
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $message
     * @return this
    */
    public final function message($message) {
        $this->message = $message;
        return $this;
    }
    /**
     * 设置data
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $data
     * @return this
    */
    public final function data($data) {
        $this->data = $data;
        return $this;
    }
    /**
     * 选择使用哪个场景
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param string $scene
     * @return this
    */
    public final function scene($scene) {
        $this->use_scene = $scene;
        return $this;
    }
    /**
     * 移除字段
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $str
     * @return void
    */
    public final function filter($str) {
        if (!$this->use_scene || empty($str)) return $this;
        // 只限制使用字段
        if (isset($this->scene[$this->use_scene][0])) {
            $list = $this->scene[$this->use_scene];
            unset($this->scene[$this->use_scene]);
            $this->scene[$this->use_scene]['field'] = $list;
        } 
        $this->scene[$this->use_scene]['filter'] = explode(',', str_replace(' ', '', $str));
        return $this;
    }
    /**
     * 添加字段
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $str
     * @return void
    */
    public final function addfield($str) {
        if (!$this->use_scene || empty($str)) return $this;

        $this->scene[$this->use_scene]['field'] = explode(',', str_replace(' ', '', $str));
        return $this;
    }
    /**
     * 获得失败结果
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function errors() {
        return $this->errors;
    }
    /**
     * 执行校验，并且是否不通过
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $data
     * @return bool true.失败 1.通过
    */
    public final function fails($data=null) {
        if ($data) $this->data = $data;
        // 请求解析记录
        if ($this->header_rule) {
            foreach ($this->header_rule as $k => $v) {
                $rule_param = $this->analysis($k, $v);
                // 调用事件器
                foreach ($rule_param['rule'] as $key => $value) {
                    $field = array_shift($rule_param['field']);
                    array_unshift($value, []);
                    array_unshift($value, $this->data);
                    $res = Event::trigger($key, $value);
                    if ($res['status'] == false) {
                        $this->parse_error($rule_param, $field, $key, $res['message']);
                    }
                    if ($res['status'] == false && $this->batch == false) {
                        return true;
                    }
                }
            }
            if (empty($this->errors)) return false;
        }
        // 如果是单条记录
        if (is_null($this->field) == false) {
            $this->rule = [
                $this->field => $this->rule,
            ];
            
            $message = [];
            foreach ($this->message as $key => $value) {
                if (strpos($key, '.') === false) {
                    $message[$this->field.'.'.$key] = $value;
                } else {
                    $message[$key] = $value;
                }
            }
            $this->message = $message;

            if (is_array($this->alias) == false) {
                $this->alias = [
                    $this->field => $this->alias,
                ];
            }
        // 使用场景值
        } else if ($this->use_scene){
            if (!isset($this->scene[$this->use_scene])) {
                throw new \Exception("Vaildate：Scene {$this->use_scene} does not exist！");
                return true;
            }
            $scene = $this->scene[$this->use_scene];
            // 只限制使用字段
            if (isset($scene[0])) {
                $list = [];
                foreach ($scene as $key) {
                    if (isset($this->rule[$key])) {
                        $list[$key] = $this->rule[$key];
                    }
                }
                $this->rule = $list;
            } else {
                $list = [];
                // 移除字段
                if (isset($scene['filter'])) {
                    foreach ($scene['filter'] as $key) {
                        if (isset($this->rule[$key])) {
                            unset($this->rule[$key]);
                        }
                    }
                }
                // 限制字段
                if (isset($scene['field'])) {
                    $list = [];
                    foreach ($scene['field'] as $key) {
                        if (isset($this->rule[$key])) {
                            $list[$key] = $this->rule[$key];
                        }
                    }
                    $this->rule = $list;
                }
                // 删除验证规则
                if (isset($scene['delete_rule'])) {
                    foreach ($scene['delete_rule'] as $key=>$val) {
                        if (isset($this->rule[$key])) {
                            $arr = explode('|', str_replace(' ', '', $val));
                            foreach ($arr as $value) {
                                $this->rule[$key] = preg_replace(['/'.$value.'(.*)\|/', '/'.$value.'(.*)/'], '', $this->rule[$key]);
                            }
                        }
                    }
                }
                // 添加校验规则
                if (isset($scene['add_rule'])) {
                    foreach ($scene['add_rule'] as $key=>$val) {
                        if (!empty($this->rule[$key])) {
                            $this->rule[$key] .= '|'.$val;
                        } else {
                            $this->rule[$key] = $val;
                        }
                    }
                }
            }
        }
        // 解析记录
        foreach ($this->rule as $k => $v) {
            $rule_param = $this->analysis($k, $v);
            // 检测是否带必填
            $firstKey = array_key_first($rule_param['rule']);
            $status = true;
            $param = null;
            // 不必填的，只有必填情况下才会进入判断
            if ($firstKey != 'require') {
                foreach ($rule_param['field'] as $field) {
                    if ($param == null) {
                        if (!isset($this->data[$field])) {
                            $status = false;
                            break;
                        }
                        $param = $this->data[$field];
                    } else {
                        if (!isset($param[$field])) {
                            $status = false;
                            break;
                        }
                        $param = $param[$field];
                    }
                }
            } else {
                foreach ($rule_param['field'] as $field) {
                    if ($param == null) {
                        if (!isset($this->data[$field])) {
                            $res = Event::trigger('require', []);
                            $this->parse_error($rule_param, $field, 'require', $res['message']);
                            if ($this->batch == false) {
                                return true;
                            }
                            $status = false;
                            break;
                        }
                        $param = $this->data[$field];
                    } else {
                        if (!isset($param[$field])) {
                            $res = Event::trigger('require', []);
                            $this->parse_error($rule_param, $field, 'require', $res['message']);
                            if ($this->batch == false) {
                                return true;
                            }
                            $status = false;
                            break;
                        }
                        $param = $param[$field];
                    }
                }
            }
            // 通过校验条件
            if (($status == true && $param !== null) || $rule_param['status'] == true) {
                // 调用事件器
                foreach ($rule_param['rule'] as $key => $value) {
                    if (in_array(strtolower($key), $this->overall)) {
                        $param = null;
                    }
                    array_unshift($value, $param);
                    array_unshift($value, $this->data);
                    $res = Event::trigger($key, $value);
                    if ($res['status'] == false) {
                        $this->parse_error($rule_param, $field, $key, $res['message']);
                    }
                    if ($res['status'] == false && $this->batch == false) {
                        return true;
                    }
                }
            } 
        }
        
        if (!empty($this->errors)) return true;

        return false;
    }

    /**
     * 解析错误日志
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param array $rule_param 字段信息
     * @param string $field 字段名
     * @param string $rule_name 规则名称
     * @param string $message 系统内置的提示语
     * @return void
    */
    private final function parse_error($rule_param, $field, $rule_name, $message) {
        if (!empty($rule_param['message'][$rule_name])) $message = $rule_param['message'][$rule_name];

        $alias = $rule_param['alias'] ? $rule_param['alias'] : $rule_param['intact_field'];

        // 先替换系统的占位符
        $message = str_replace(['{:preset}', '{'.$field.'}'], $alias, $message);
        // 再替换系统函数
        $rule = $rule_param['rule'][$rule_name];
        // 更多参数替换
        if ($rule) {
            $rule = explode(',', str_replace(' ', '', $rule[0]));
            foreach ($rule as $key => $value) {
                $message = str_replace('{'.$key.'}', $value, $message);
            }
            // 规则只有一个参数的话，也可以用规则名定义占位符
            if (count($rule) == 1) {
                $message = str_replace('{'.$rule_name.'}', current($rule), $message);
            }
        }
        
        $this->errors[] = [
            'intact_field' => $rule_param['intact_field'],
            'field' => $field,
            'rule' => $rule_name,
            'message' => $message,
        ];
    }

    /**
     * 解析规则
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-14
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @param string $value
     * @return array
    */
    private final function analysis($key, $value) {
        $ret = [
            'alias' => $this->alias[$key] ?? null,
        ];
        $status = false;
        $message = [];
        $rule = [];
        $arr = explode('|', preg_replace('/\s+/', '', $value));
        foreach ($arr as $v) {
            // 有附带参数
            if (strpos($v, ':') !== false) {
                $array = explode(':', $v);
                $v = $array[0];
                if (isset($this->conversion[$v])) {
                    $v = $this->conversion[$v];
                }
                $k = $key.'.'.$v;
                $rule[$v] = [$array[1]];
            // 没附带参数
            } else {
                $k = $key.'.'.$v;
                if (isset($this->conversion[$v])) {
                    $v = $this->conversion[$v];
                }
                $rule[$v] = [];
            }
            $message[$v] = $this->message[$k] ?? null;
            $status = in_array(strtolower($v), $this->overall);
        }
        $ret['status'] = $status;
        $ret['message'] = $message;
        $ret['rule'] = $rule;
        $ret['intact_field'] = preg_replace('/\s+/', '', $key);
        $ret['field'] = explode('.', $ret['intact_field']);

        return $ret;
    }
}
