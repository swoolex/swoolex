<?php
/**
 * +----------------------------------------------------------------------
 * Restful类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;

class Restful {
    /**
     * 自定义的Msg内容
    */
    private $diyMsg;

    /**
     * 实例化对象方法
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.06.08
     * @deprecated 暂不启用
     * @global 无
     * @return Restful
    */
    public static function run(){
        // 每次调用返回一个新的对象
        return new \x\entity\Restful();
    }

    /**
     * 设置返回值类型
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $type
     * @return this
    */
    public function type($type) {
        $this->type = $type;
        return $this;
    }
    
    /**
     * 设置格式
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $make
     * @return this
    */
    public function make($make) {
        $this->make = $make;
        return $this;
    }
    
    /**
     * 设置code值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $code
     * @return this
    */
    public function code($code) {
        $this->code = $code;
        return $this;
    }

    /**
     * 设置header值
     * @todo 无
     * @author 小黄牛
     * @version v2.5.23 + 2022.2.25
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $headers
     * @return this
    */
    public function header($headers) {
        $this->header = $headers;
        return $this;
    }

    /**
     * 设置msg值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $msg
     * @return this
    */
    public function msg($msg) {
        $this->msg = $msg;
        return $this;
    }

    /**
     * 自定义Msg
     * @todo 无
     * @author 小黄牛
     * @version v2.5.19 + 2021.12.27
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $msg
     * @return this
    */
    public function setMsg($msg) {
        $this->diyMsg = $msg;
        return $this;
    }

    /**
     * 设置data值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $data
     * @return this
    */
    public function data($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * 输出返回值
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $code 对应code()
     * @param mixed $msg 对应msg()
     * @param mixed $data 对应data()
     * @return void
    */
    public function callback($code='swoolex_no', $msg='swoolex_no', $data='swoolex_no') {
        // 修改默认值
        if ($code != 'swoolex_no') $this->code = $code;
        if ($msg != 'swoolex_no')  $this->msg = $msg;
        if ($data != 'swoolex_no') $this->data = $data;

        // 判断参数是否有植入
        if (empty($this->code)) {
            throw new \Exception("Restful is missing a status code parameter!");
            return true;
        }

        // 读取配置文件
        $config = require ROOT_PATH.'/restful/config.php';
        if (empty($this->type)) $this->type = $config['type'];
        if (empty($this->make)) $this->make = 'default';

        // 处理默认值大小写问题
        $this->type = strtolower($this->type);
        // 判断类型
        if (in_array($this->type, ['json', 'xml']) == false) {
            throw new \Exception("Restful is Bad return value data format：".$this->type);
            return true;
        }
        $this->make = strtolower($this->make);
        // 判断返回值结构
        if (!isset($config[$this->make])) {
            throw new \Exception("Restful is Nonexistent return value structure configuration item：".$this->make);
            return true;
        }
        // 读取返回值结构
        $this->structure = $config[$this->make];
        // 获取配置
        $code = require ROOT_PATH.'/restful/'.$this->make.'/code.php';
        $msg = require ROOT_PATH.'/restful/'.$this->make.'/msg.php';

        // 判断状态码是否存在
        if (!isset($code[$this->code])) {
            throw new \Exception("Restful is Status code does not exist：".$this->code);
            return true;
        }

        // 查找tips
        if ($this->diyMsg) {
            $tips = $this->diyMsg;
        } else {
            if (!isset($msg[$this->code])) {
                throw new \Exception("Restful is Status msg does not exist：".$this->code);
                return true;
            }
            if (!empty($this->msg)) {
                if (!isset($msg[$this->code][$this->msg])) {
                    throw new \Exception("Restful is Status msg does not exist：".$this->code.'["'.$this->msg.'"]');
                    return true;
                }
                $tips = $msg[$this->code][$this->msg];
            } else {
                if (!isset($msg[$this->code]['default'])) {
                    throw new \Exception("Restful is Status msg does not exist：".$this->code.'["default"]');
                    return true;
                }
                $tips = $msg[$this->code]['default'];
            }
        }

        $ret_data = isset($this->data) ? $this->data : $config[$this->make]['set'];
        // 类型强制转换
        if ($config[$this->make]['force']) {
            if (is_array($ret_data)) {
                $ret_data = $this->loop($ret_data);
            } else {
                if (filter_var($ret_data, FILTER_VALIDATE_INT) || $ret_data === '0') {
                    $ret_data = (int)$ret_data;
                } else if (filter_var($ret_data, FILTER_VALIDATE_FLOAT) || $ret_data == '0.0' || $ret_data == '0.00') {
                    $ret_data = (double)$ret_data;
                } else if ($ret_data === '') {
                    $ret_data = null;
                }
            }
        }
        // 组装返回值
        $return = [
            $config[$this->make]['status'] => $code[$this->code],
            $config[$this->make]['tips'] => $tips,
            $config[$this->make]['result'] => $ret_data,
        ];

        // 根据返回值判断输出类型
        $Response = \x\context\Response::get();
        // 读取默认响应头
        if (empty($this->header)) $this->header = $config[$this->make]['headers'];
        // 自定义响应头
        if (!empty($this->header) && is_array($this->header)) {
            foreach ($this->header as $key=>$value) {
                $Response->header($key, $value);
            }
        }

        if ($this->type == 'json') {
            $Response->header('Content-type', 'application/json;charset=utf-8');
            $return = json_encode($return, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $Response->header('Content-type', 'text/xml;charset=utf-8');
            $xml = $this->xmlToEncode($return);
            $return  ="<?xml version='1.0' encoding='UTF-8'?>";
            $return .= "<root>";
            $return .= $xml;
            $return .= "</root>";
        }

        return $Response->end($return);
    }

    /**
     * 魔术方法
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
    */
    public function __call($funName, $arguments) {
		return $funName;
    }
    
    /**
     * 数组转XML
     * @todo 无
     * @author 小黄牛
     * @version v2.0.8 + 2021.6.8
     * @deprecated 暂不启用
     * @global 无
     * @param array $data
     * @return xml
    */
    public function xmlToEncode($data){
        $xml = $attr = '';
        foreach ($data as $key=>$value) {
            if(is_numeric($key)){
                $attr = "id='{$key}'";
                $key = "item";
            }
            $xml .= "<{$key} {$attr}>";
            $xml .= is_array($value) ? $this->xmlToEncode($value) : $value;
            $xml .= "</{$key}>";
        }
        return $xml;
    }
    /**
     * 递归多级菜数组
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param array $array 数组
     * @return 
    */
    private function loop($array) {
        foreach ($array as $k=>$v) {
            if (is_array($v)) {
                $array[$k] = $this->loop($v);
            } else {
                if (filter_var($v, FILTER_VALIDATE_INT) || $v === '0') {
                    $array[$k] = (int)$v;
                } else if (filter_var($v, FILTER_VALIDATE_FLOAT) || $v == '0.0' || $v == '0.00') {
                    $array[$k] = (float)$v;
                } else if ($v === '') {
                    $array[$k] = null;
                }
            }
        }
        return $array;
    }
}