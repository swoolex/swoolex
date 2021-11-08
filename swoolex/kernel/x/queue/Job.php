<?php
/**
 * +----------------------------------------------------------------------
 * 队列生产者
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\queue;

abstract class Job
{
    /**
     * 任务ID
    */
    private $uuid;
    /**
     * 驱动
    */
    private $DriverName = 'default';

    // 驱动类名
    private $DriverClass;
    // 驱动所使用的连接池标识
    private $pool;
    // 队列前缀
    private $channel;
    // 消费的超时时间（S）
    private $timeout;
    // 消费失败后的间隔次数+间隔时间
    private $retry_seconds;
    
    // 等待确认投递时间（S）
    private $wait_time = 0;
    // 等待确认超时时间
    private $wait_end_time;
    // 延迟队列投递时间（S）
    private $delay_time = 0;
    // 投递数据集
    private $param;

    // 当前重试次数
    private $retry_num = 0;

    /**
     * 初始化配置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct() {
        $this->uuid = \x\Snowflake::create();
        $this->saveConfig();
    }

    /**
     * 生产者必须自定义消费逻辑
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function handle();

    /**
     * 执行消费逻辑
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final public function run() {
        $config = \x\Config::get('queue.'.$this->DriverName);
        $QueueDriver = new $this->DriverClass($config);
        
        try{
            // 计算消费耗时
            $start_time = microtime(true); 
            $res = $this->handle();
            $end_time = microtime(true); 
            // 记录到超时队列
            if ($this->timeout < ($end_time-$start_time)) {
                $QueueDriver->JobOuttime($this);
            } else {
                // 如果消费返回false，则进入重试通知
                if ($res === false) {
                    if (count($this->retry_seconds) != $this->retry_num) {
                        $this->retry_num++;
                        $QueueDriver->JobRetry($this);
                    } else {
                        // 消费逻辑发生错误要通知到驱动
                        $QueueDriver->JobError($this);
                    }
                } else {
                    // 消费成功
                    $QueueDriver->JobSuccess($this);
                }
            }
            return $res;
        }catch (\Throwable $throwable){
            throw $throwable;
            if (count($this->retry_seconds) != $this->retry_num) {
                $this->retry_num++;
                $QueueDriver->JobRetry($this);
            } else {
                // 消费逻辑发生错误要通知到驱动
                $QueueDriver->JobError($this);
            }
            return false;
        }
    }

    /**
     * 指定队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列标识符
     * @return void
    */
    final public function store($type) {
        $this->DriverName = $type;
        $this->saveConfig();
        return $this;
    }
    
    /**
     * 设置消费超时时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param int $s 秒
     * @return void
    */
    final function outTime($s) {
        $this->timeout = $s;
        return $this;
    }
    
    /**
     * 设置延迟投递时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param int $s 秒
     * @return void
    */
    final function delayTime($s) {
        $this->delay_time = $s;
        return $this;
    }

    /**
     * 获取延迟投递时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final function getDelayTime() {
        return $this->delay_time;
    }

    /**
     * 设置等待投递时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param int $s 秒
     * @return void
    */
    final function waitTime($s) {
        $this->wait_time = $s;
        $this->wait_end_time = time()+$s;
        return $this;
    }

    /**
     * 获取等待投递时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final function getWaitTime() {
        return $this->wait_time;
    }

    /**
     * 获取等待超时时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final function getWaitEndTime() {
        return $this->wait_end_time;
    }

    /**
     * 设置消费失败后的重试次数+间隔时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param array [$s] 秒
     * @return void
    */
    final function retrySeconds($array_time) {
        $this->retry_seconds = $array_time;
        return $this;
    }

    /**
     * 设置投递数据
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $data
     * @return void
    */
    final public function data($data) {
        $this->param = $data;
        return $this;
    }

    /**
     * 获取投递数据
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $data
     * @return void
    */
    final public function param() {
        return $this->param;
    }

    /**
     * 获取任务ID
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final public function uuid() {
        return $this->uuid;
    }

    /**
     * 获取下一次重试的间隔时间
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    final public function retry_time() {
        $key = $this->retry_num-1; 
        return $this->retry_seconds[$key] ?? false;
    }

    /**
     * 更新生产者配置信息
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function saveConfig() {
        $config = \x\Config::get('queue.'.$this->DriverName);
        $this->config = $config;
        $this->DriverClass = '\x\queue\driver\\'. ucfirst(strtolower($config['type']));
        $this->pool = $config['pool'];
        $this->channel = $config['channel'];
        $this->timeout = $config['timeout'];
        $this->retry_seconds = $config['retry_seconds'];
    }
}
