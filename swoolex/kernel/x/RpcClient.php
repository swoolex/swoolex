<?php
/**
 * +----------------------------------------------------------------------
 * 微服务-客户端调用类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class RpcClient {
    /**
     * 请求结果 true|false
    */
    private $status = false;
    /**
     * 返回说明
    */
    private $msg = 'ERROR';
    /**
     * 返回结果
    */
    private $data;
    /**
     * 请求开始时间
    */
    private $start_time;
    /**
     * 请求超时时间(S)
    */
    private $out_time;
    /**
     * 发送次数
    */
    private $send_num = 1;
    /**
     * 请求路由
    */
    private $class;
    /**
     * 请求方法
    */
    private $function;
    /**
     * 是否异步执行
    */
    private $task=false;
    /**
     * 请求头
    */
    private $headers = [];
    /**
     * 请求参数
    */
    private $param = [];
    /**
     * 失败时，使用最大节点次数
    */
    private $num = 1;
    /**
     * 开始请求时间
    */
    private $start_ms;
    /**
     * 当前请求配置
    */
    private $config;
    /**
     * 异步回调地址
    */
    private $callback = false;
    /**
     * 异步回调请求类型
    */
    private $callback_type = 'post';
    
    /**
     * 标记开始时间
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct() {
        $this->start_time = time();
        $this->start_ms = microtime(true);
        $this->out_time = \x\Config::get('rpc.out_time');
    }

    /**
     * 判断单个请求延迟
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __destruct() {
        if (!empty($this->config['max_ms'])) {
            $end_ms = microtime(true);
            $ms = ($end_ms-$this->start_ms)*1000;
            if ($ms >= $this->config['max_ms']) {
                $msg  = '请求耗时（ms）：'.$ms.PHP_EOL;
                $msg .= '请求路由：'.$this->class.PHP_EOL;
                $msg .= '请求方法：'.$this->function.PHP_EOL;
                $msg .= '请求头：'.json_encode($this->headers, JSON_UNESCAPED_UNICODE).PHP_EOL;
                $msg .= '请求参数：'.json_encode($this->param, JSON_UNESCAPED_UNICODE).PHP_EOL;
                $msg .= '请求节点：'.json_encode($this->config, JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;

                $dir = WORKLOG_PATH.'rpc'.DS;
                if (is_dir($dir) == false) {
                    mkdir($dir, 0755);
                }

                $file_path = $dir.date('Ymd').'.log';
                // 写入日志记录
                \Swoole\Coroutine\System::writeFile($file_path, $msg, FILE_APPEND);
            }
        }
    }

    /**
     * 设置参数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $key
     * @param mixed $val
     * @return void
    */
    public function set($key, $val) {
        $this->$key = $val;
        return $this;
    }

    /**
     * 设置路由地址
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $class 请求路由
     * @return void
    */
    public function route($class) {
        $this->class = $class;
        return $this;
    }

    /**
     * 设置请求方法
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $function 请求方法
     * @return void
    */
    public function func($function) {
        $this->function = $function;
        return $this;
    }

    /**
     * 设置为异步任务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function task() {
        $this->task = true;
        return $this;
    }
    
    /**
     * 设置请求头
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param array $header 请求头
     * @return void
    */
    public function header($header) {
        $this->headers = $header;
        return $this;
    }

    /**
     * 设置请求参数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param array $param 参数
     * @return void
    */
    public function param($param) {
        $this->param = $param;
        return $this;
    }

    /**
     * 设置最大请求次数
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $num 次数
     * @return void
    */
    public function max($num) {
        $this->num = $num;
        return $this;
    }

    /**
     * 设置异步任务的回调通知地址
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $url
     * @param string $type
     * @return void
    */
    public function callback($url, $type='post') {
        $this->callback = $url;
        $this->callback_type = strtolower($type);
        return $this;
    }

    /**
     * 请求微服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return mixed
    */
    public function send() {
        if ((time()-$this->start_time) >= $this->out_time) {
            $this->msg = "rpc request timeout";
            $this->status = '408';
            return false;
        }

        $list = Rpc::run()->get($this->class, $this->function);
        if (empty($list)) {
            $this->msg = "rpc service 【".$this->class." ".$this->function."】 not registered";
            return false;
        }

        // 递归到最后一个节点了
        if ($this->num < $this->send_num) {
            return false;
        }
        // 权重获取
        $config = $this->weightConfig($list);
        if ($config == false) {
            $this->msg = "rpc The service has been completely stopped";
            Rpc::run()->ping_error(['class'=>$this->class, 'function'=>$this->function], 3);
            return false;
        }

        // 发送请求
        $res = $this->run($config);
        if ($this->status != '200') {
            return $this->send();
        }

        return $res;
    }

    // 发送微服务请求
    private function run($config) {
        $this->config = $config;
        $this->send_num++;

        // 更新当前请求数
        $md5 = md5($config['class'].$config['function'].$config['ip'].$config['port']);
        $num_key = \x\Config::get('rpc.redis_key').'_num_'.$md5;
        $redis = new \x\Redis();
        $redis->INCR($num_key); 

        $data = json_encode([
            'class' => $this->class,
            'function' => $this->function,
            'headers' => $this->headers,
            'ip' => $config['ip'],
            'port' => $config['port'],
            'param' => $this->param,
            'task' => $this->task,
            'callback' => $this->callback,
            'callback_type' => $this->callback_type,
        ], JSON_UNESCAPED_UNICODE);

        $rpc = \x\Config::get('rpc');
        // 数据加密
        if ($rpc['aes_status'] == true) {
            $Currency = new \x\rpc\Currency();
            $data = $Currency->aes_encrypt($data);
            unset($Currency);
        }
        // 调用服务
        $client = new \Swoole\Coroutine\Client(SWOOLE_SOCK_TCP);
        $client->set(array(
            'package_length_offset' => $rpc['package_length_offset'],
            'package_body_offset'   => $rpc['package_body_offset'],
            'package_max_length'    => $rpc['package_max_length'],
            'timeout'               => $rpc['timeout'],
            'connect_timeout'       => $rpc['connect_timeout'],
            'write_timeout'         => $rpc['write_timeout'],
            'read_timeout'          => $rpc['read_timeout'],
        ));
        if (!$client->connect($config['ip'], $config['port'], 1)) {
            $redis->DECR($num_key); 
            $redis->return();
            // 这里理应关闭该连接，标记is_fault
            $config['is_fault'] = 1;
            \x\Rpc::run()->set($config);
            \x\Rpc::run()->ping_error($config, 4);
            $this->msg = 'connect failed. Error: '.$client->errCode;
            $client->close();
            return false;
        }
        $client->send($data);
        $body = $client->recv();
        $client->close();

        if (!$body) {
            $redis->DECR($num_key); 
            $redis->return();
            // 这里理应关闭该连接，标记is_fault
            $config['is_fault'] = 1;
            \x\Rpc::run()->set($config);
            \x\Rpc::run()->ping_error($config, 5);
            $this->msg = 'connect return body Error';
            return false;
        }

        // 请求数-1
        $redis->DECR($num_key); 
        $redis->return();

        // 数据解密
        if ($rpc['aes_status'] == true) {
            $Currency = new \x\rpc\Currency();
            $body = $Currency->aes_decrypt($body);
            unset($Currency);
        }
        $body = json_decode($body, true);
        $this->status = $body['status'];
        $this->msg = $body['msg'];
        $this->data = $body['data'];

        if ($this->status == '200') {
            if ($this->task) {
                $this->msg = 'Task Success';
            }
            return $this->data;
        }

        return false;
    }

    // 权重获取
    private function weightConfig($list) {
        // 检测是不是刚初始化SW-X的时候
        if (empty($list[0]['ping_ms']) && empty($list[0]['is_fault'])) {
            return $list[0];
        }
        // 先删除已经不行的代码
        $yes_list = [];
        foreach ($list as $k=>$v) {
            if (isset($v['is_fault']) && $v['is_fault'] == 0) {
                $yes_list[] = $v;
            } else if (empty($v['is_fault']) && empty($v['status'])) {
                $yes_list[] = $v;
            }
        }
        // 多维数组排序
        // 评分最高，调用人数最低，延迟最低
        $score = [];
        $request_num = [];
        $ping_ms = [];
        foreach ($yes_list as $v) {
            $score[] = $v['score'] ?? 100;
            $request_num[] = $v['request_num'] ?? 0;
            $ping_ms[] = $v['ping_ms'] ?? 0;
        }

        $bool = array_multisort($score, SORT_DESC, $request_num, SORT_ASC, $ping_ms, SORT_ASC, $yes_list);
        return array_shift($yes_list);
    }

    // 以下为获取相关状态
    public function getMsg() {
        return $this->msg;
    }
    public function getStatus() {
        return $this->status;
    }
    public function isSuccess() {
        if ($this->status == 200) {
            return true;
        }
        return false;
    }
}