<?php
// +----------------------------------------------------------------------
// | 微服务-客户端调用类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class RpcClient
{   
    /**
     * 请求结果 true|false
    */
    private $status = false;
    /**
     * 返回状态码
    */
    private $code = '500';
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
     * 开始请求时间
    */
    private $start_ms;
    /**
     * 当前请求配置
    */
    private $config;


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
        $this->out_time = \x\Config::run()->get('rpc.out_time');
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

                $dir = ROOT_PATH.'/runtime/rpc/';
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
    }

    /**
     * 请求微服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param string $class 请求路由
     * @param string $function 请求方法
     * @param array $headers 请求头
     * @param array $param 请求参数
     * @param int $num 请求次数
     * @param bool $task 是否异步执行
     * @return mixed
    */
    public function run($class, $function, $param=[], $headers=[], $num=1, $task=false) {
        $this->class = $class;
        $this->function = $function;
        $this->param = $param;
        $this->headers = $headers;
        $this->task = $task;

        if ((time()-$this->start_time) >= $this->out_time) {
            $this->msg = "rpc request timeout";
            $this->code = '408';
            return false;
        }

        $list = Rpc::run()->get($class);
        if (empty($list[$function])) {
            $this->msg = "rpc service 【".$class." ".$function."】 not registered";
            return false;
        }

        // 权重获取
        $list = $list[$function];
        // 递归到最后一个节点了
        if ($num < $this->send_num) {
            return false;
        }
        $config = $this->weightConfig($list);
        if ($config == false) {
            $this->msg = "rpc The service has been completely stopped";
            Rpc::run()->ping_error($class, $function, $list, 3);
            return false;
        }

        // 发送请求
        $res = $this->send($config, $class, $function, $headers, $param);
        if ($res === false) {
            return $this->run($class, $function, $param, $headers, $num, $task);
        }

        return $res;
    }

    // 发送微服务请求
    private function send($config, $class, $function, $headers=[], $param=[]) {
        $this->config = $config;
        $this->send_num++;

        // 更新当前请求数
        $config['request_num'] = isset($config['request_num']) ? ($config['request_num']+1) : 1;
        Rpc::run()->setOne($class, $function, $config);

        $data = json_encode([
            'class' => $class,
            'function' => $function,
            'headers' => $headers,
            'param' => $param,
            'task' => $this->task,
        ], JSON_UNESCAPED_UNICODE);

        $rpc = \x\Config::run()->get('rpc');
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
            // 这里理应关闭该连接，标记is_fault
            $config['is_fault'] = 1;
            $config['request_num'] -= 1;
            \x\Rpc::run()->setOne($class, $function, $config);
            \x\Rpc::run()->ping_error($class, $function, $config, 4);
            $this->msg = 'connect failed. Error: '.$client->errCode;
            $client->close();
            return false;
        }
        $client->send($data);
        $body = $client->recv();
        $client->close();
        if (!$body) {
            // 这里理应关闭该连接，标记is_fault
            $config['is_fault'] = 1;
            $config['request_num'] -= 1;
            \x\Rpc::run()->setOne($class, $function, $config);
            \x\Rpc::run()->ping_error($class, $function, $config, 5);
            $this->msg = 'connect return body Error';
            return false;
        }

        // 请求数-1
        $config['request_num'] -= 1;
        \x\Rpc::run()->setOne($class, $function, $config);

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
            } else {
                $this->msg = 'Success';
            }
            return $this->data;
        }

        return false;
    }

    // 权重获取
    private function weightConfig($list) {
        // 检测是不是刚初始化SW-X的时候
        if (empty($list[0]['ping_ms']) && empty($list[0]['is_fault'])) return $list[0];
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
    public function getCode() {
        return $this->code;
    }
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