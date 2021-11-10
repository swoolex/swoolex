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
        // 投递时间
        $Job->push_time = time();
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

    //------------------------------ 透析队列支持 -------------------------------
    /**
     * 获得某个Job的详情
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-08
     * @deprecated 暂不启用
     * @global 无
     * @param string $uuid 生产者ID
     * @return Job
    */
    public function info($uuid) {
        $Job = $this->Redis->hGet($this->_key_entity, $uuid);
        $this->Redis->return();
        if (!$Job) return null;
        return unserialize($Job);
    }

    /**
     * 查看队列数量
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-08
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @return int
    */
    public function count($type) {
        $type = strtolower($type);
        $key = '_key_'.$type;

        switch ($type) {
            case 'waiting': $count = $this->Redis->lLen($this->$key);break;
            case 'delayed':$count = $this->Redis->zCard($this->$key);break;
            default: $count = $this->Redis->hLen($this->$key);break;
        }

        $this->Redis->return();
        return $count;
    }
     /**
     * 队列记录分页查询
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @param int $page 当前页数
     * @param int $limit 记录数
     * @return array
    */
    public function page($type, $page, $limit) {
        $type = strtolower($type);
        $key = '_key_'.$type;
        $ret = [];

        $page = ($page>0) ? ($page-1) : 0;
        $left = $page*$limit;
        switch ($type) {
            case 'waiting': 
                $list = $this->Redis->lRange($this->$key, $left, ($left+$limit-1)); 
                foreach ($list as $key => $uuid) {
                    $Job = $this->Redis->hGet($this->_key_entity, $uuid);
                    if ($Job) {
                        $ret[] = unserialize($Job);
                    }
                }
            break;
            case 'delayed':
                $list = $this->Redis->zRange($this->$key, $left, ($left+$limit-1));
                foreach ($list as $time => $uuid) {
                    $Job = $this->Redis->hGet($this->_key_entity, $uuid);
                    if ($Job) {
                        $ret[] = unserialize($Job);
                    }
                }
            break;
            default: 
                $list = $this->Redis->hKeys($this->$key);
                $i = 0;
                $max = $left+$limit;
                foreach ($list as $time => $uuid) {
                    if ($i >= $left && $i < $max) {
                         $Job = $this->Redis->hGet($this->_key_entity, $uuid);
                        if ($Job) {
                            $ret[] = unserialize($Job);
                        }
                    } else if ($i >= $max){
                        break;
                    }
                    $i++;
                }
            break;
        }
        $this->Redis->return();
        return $ret;
    }
    /**
     * 删除某条队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @param string $uuid 生产者ID
     * @return bool
    */
    public function delete($type, $uuid) {
        $type = strtolower($type);
        $key = '_key_'.$type;

        switch ($type) {
            case 'waiting': $res = $this->Redis->lRem($this->$key, -1, $uuid);break;
            case 'delayed':$res = $this->Redis->zRem($this->$key, $uuid);break;
            default: $res = $this->Redis->hDel($this->$key, $uuid);break;
        }
        if ($res) {
            $res = $this->Redis->hDel($this->_key_entity, $uuid);
        }
        $this->Redis->return();
        return $res;
    }
    /**
     * 清除整个队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @return 无
    */
    public function clear($type) {
        $type = strtolower($type);
        $key = '_key_'.$type;

        switch ($type) {
            case 'waiting': 
                $list = $this->Redis->lRange($this->$key, 0, -1); 
                foreach ($list as $key => $uuid) {
                    $res = $this->Redis->lRem($this->$key, -1, $uuid);
                    if ($res) $this->Redis->hDel($this->_key_entity, $uuid);
                }
            break;
            case 'delayed':
                $list = $this->Redis->zRange($this->$key, 0, -1);
                foreach ($list as $time => $uuid) {
                    $res = $this->Redis->zRem($this->$key, $uuid);
                    if ($res) $this->Redis->hDel($this->_key_entity, $uuid);
                }
            break;
            default: 
                $list = $this->Redis->hKeys($this->$key);
                foreach ($list as $k => $uuid) {
                    $res = $this->Redis->hDel($this->$key, $uuid);
                    if ($res) $this->Redis->hDel($this->_key_entity, $uuid);
                }
            break;
        }
        $this->Redis->return();
        return true;
    }

    /**
     * 把某条队列加入待处理队列中
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @param string $uuid 生产者ID
     * @return bool
    */
    public function move($type, $uuid) {
        $type = strtolower($type);
        $key = '_key_'.$type;

        switch ($type) {
            case 'delayed':
                $res = $this->Redis->zRem($this->$key, $uuid);
                if ($res) {
                    // 插入到队列头
                    $res = $this->Redis->lPush($this->_key_waiting, $uuid);
                }
            break;
            default: 
                $res = $this->Redis->hDel($this->$key, $uuid);
                if ($res) {
                    // 插入到队列头
                    $res = $this->Redis->lPush($this->_key_waiting, $uuid);
                }
            break;
        }
        $this->Redis->return();
        return $res;
    }
    /**
     * 把整个队列加入待处理队列中
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 队列名称
     * @return 无
    */
    public function moves($type) {
        $type = strtolower($type);
        $key = '_key_'.$type;

        switch ($type) {
            case 'delayed':
                $list = $this->Redis->zRange($this->$key, 0, -1);
                foreach ($list as $time => $uuid) {
                    $res = $this->Redis->zRem($this->$key, $uuid);
                    if ($res) {
                        // 插入到队列头
                        $res = $this->Redis->lPush($this->_key_waiting, $uuid);
                    }
                }
            break;
            default: 
                $list = $this->Redis->hKeys($this->$key);
                foreach ($list as $k => $uuid) {
                    $res = $this->Redis->hDel($this->$key, $uuid);
                    if ($res) {
                        // 插入到队列头
                        $res = $this->Redis->lPush($this->_key_waiting, $uuid);
                    }
                }
            break;
        }
        $this->Redis->return();
        return true;
    }
    
    /**
     * 初始化队列
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-09
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public function initialize() {
        $this->Redis->del($this->_key_confirm);
        $this->Redis->del($this->_key_waiting);
        $this->Redis->del($this->_key_reserved);
        $this->Redis->del($this->_key_delayed);
        $this->Redis->del($this->_key_failed);
        $this->Redis->del($this->_key_timeout);
        $this->Redis->del($this->_key_entity);
        return true;
    }
}
