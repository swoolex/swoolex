<?php
/**
 * +----------------------------------------------------------------------
 * 队列 - Redis驱动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\queue\driver;
use design\AbstractQueueDriver;
use Swoole\Coroutine;
use Swoole\Timer;
use x\Redis as RedisClient;

class Redis extends AbstractQueueDriver
{
    /**
     * Redis实例
    */
    private $Redis;

    /**
     * 获得Redis实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct($config) {
        parent::__construct($config);
        $this->Redis = new RedisClient();
    }

    /**
     * 投递任务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function push($Job) {
        $uuid = $Job->uuid();
        $this->Redis->hSet($this->_key_entity, $uuid, serialize($Job));

        $waitTime = $Job->getWaitTime();
        $delayTime = $Job->getDelayTime();
        // 延迟队列
        if($delayTime > 0 && $waitTime <= 0){
            $res = $this->Redis->zAdd($this->_key_delayed, time()+$delayTime, $uuid);
            $this->Redis->return();
            return $res;
        }
        
        // 等待确认投递队列
        if($waitTime > 0) {// 待确认
            Timer::after(($waitTime*1000), function() use ($uuid) {
                $Redis = new RedisClient();
                $Redis->hDel($this->_key_confirm, $uuid);
                $Redis->return();
            });
            $res = $this->Redis->hSet($this->_key_confirm, $uuid, 1);
            $this->Redis->return();
            return $res;
        }
        
        // 直接投递到待消费队列
        $res = $this->Redis->rPush($this->_key_waiting, $uuid);
        $this->Redis->return();
        return $res;
    }

    /**
     * 获取一个任务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function pop() {
        // 先查看延时队列
        Coroutine::create(function () {
            $maxTime = time();
            $list = $this->Redis->zCount($this->_key_delayed, 0, $maxTime);
            if ($list) {
                $jobs = $this->Redis->zPopmin($this->_key_delayed, $list);
                if (is_array($jobs)) {
                    foreach ($jobs as $uuid => $time){
                        if($time > $maxTime){
                            $this->Redis->zAdd($this->_key_delayed, $time, $uuid);
                        }else{
                            // 插入到队列头
                            $this->Redis->lPush($this->_key_waiting, $uuid);
                        }
                    }
                }
            }
        });

        // 从待消费队列中取出
        $uuid = $this->Redis->lPop($this->_key_waiting);
        if (!$uuid) {
            $this->Redis->return();
            return null;
        }
        // 获得实体
        $Job = $this->Redis->hGet($this->_key_entity, $uuid);
        if (!$Job) {
            $this->Redis->return();
            return null;
        }
        if(!$Job){
            $this->Redis->return();
            return null;
        }
        $Job = unserialize($Job);
        // 加入消费中队列
        $this->Redis->hSet($this->_key_reserved, $uuid, 0);
        $this->Redis->return();
        return $Job;
    }

    /**
     * 确认一个任务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function confirm($Job) {
        $uuid = $Job->uuid();
        $status = $this->Redis->hGet($this->_key_confirm, $uuid);
        // 只有待确认才正常
        if ($status != 1) {
            $this->Redis->return();
            return false;
        }
        // 删除状态
        $this->Redis->hDel($this->_key_confirm, $uuid);
        // 超时确认
        if ($Job->getWaitEndTime() < time()) {
            $res = $this->Redis->hDel($this->_key_entity, $uuid);
            $this->Redis->return();
            return $res;
        }
        // 正常确认
        // 插入到队列头
        $res = $this->Redis->lPush($this->_key_waiting, $uuid);
        $this->Redis->return();
        return $res;
    }

    /**
     * 把该任务投递到重试队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function JobRetry($Job) {
        $delayTime = $Job->retry_time();
        if (!$delayTime) return false;

        $uuid = $Job->uuid();
        // 投递到延迟队列
        $res = $this->Redis->zAdd($this->_key_delayed, time()+$delayTime, $uuid);
        $this->Redis->return();
        return $res;
    }

    /**
     * 把该任务投递到失败队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function JobError($Job) {
        $uuid = $Job->uuid();
        $res = $this->Redis->hSet($this->_key_failed, $uuid, 0);
        $this->Redis->return();
        return $res;
    }
    
    /**
     * 把该任务投递到消费超时队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-08
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function JobOuttime($Job) {
        $uuid = $Job->uuid();
        $res = $this->Redis->hSet($this->_key_timeout, $uuid, 0);
        $this->Redis->return();
        return $res;
    }

    /**
     * 消费成功后删除队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-08
     * @deprecated 暂不启用
     * @global 无
     * @param Job $Job
     * @return void
    */
    public function JobSuccess($Job) {
        $uuid = $Job->uuid();
        $this->Redis->hDel($this->_key_entity, $uuid);
        $res = $this->Redis->hDel($this->_key_reserved, $uuid);
        $this->Redis->return();
        return $res;
    }
}
