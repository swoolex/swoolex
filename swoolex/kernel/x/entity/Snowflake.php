<?php
/**
 * +----------------------------------------------------------------------
 * 雪花分布式唯一自增ID生成器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;
use design\AbstractSingleCase;
use x\Config;
use Swoole\Lock;

class Snowflake 
{
    use AbstractSingleCase;

    /**
     * 开始时间戳
    */
    private $epoch = 1633017600000;
    /**
     * 序号防泄漏
    */
    private $sequenceMask = 8191;
    /**
     * 据标识id（业务id）
    */
    private $dataCenterId;
    /**
     * 机器id
    */
    private $workerId;
    /**
     * 上次ID生成时间戳
    */
    protected $timestamp;
    /**
     * 序号
    */
    protected $sequence;

    /**
     * 设置工作进程ID
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @param int $worker_id
    */
    public function setWorkerId($worker_id) {
        $this->workerId = $worker_id;
        $this->dataCenterId = str_replace('.', '', Config::get('server.host')) . Config::get('server.port');
        $this->timestamp = 0;
        $this->sequence = 0;
        $this->lock = new Lock(SWOOLE_MUTEX);
    }

    /**
     * 生成唯一ID
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @return string
    */
    public function create() {
        if (!$this->lock) $this->lock = new Lock(SWOOLE_MUTEX);
        
        $this->lock->lockwait(1);
        
        $timestamp = $this->getTime();
        if ($this->timestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;
            if ($this->sequence == 0){
                $timestamp = $this->waitTime($this->timestamp);
            }
        } else {
            $this->sequence = 0;
        }
        $this->timestamp = $timestamp;
        
        $this->lock->unlock();
        
        return (($timestamp - $this->epoch) << 22) | ($this->dataCenterId << 17) | ($this->workerId << 10) | $this->sequence;
    }

    /**
     * 解析ID值
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @param string $ID
     * @return array
    */
    public function parse($id) {
        $Binary = str_pad(decbin($id), 64, '0', STR_PAD_LEFT);
        $timestamp = bindec(substr($Binary, 0, 42)) + $this->epoch;
        return [
            'timestamp' => $timestamp,
            'data_center_id' => bindec(substr($Binary, 42, 5)),
            'worker_id' => bindec(substr($Binary, 47, 7)),
            'sequence' => bindec(substr($Binary, -11)),
        ];
    }

    /**
     * 获取当前时间戳
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @return int
    */
    private function getTime() {
        return (int)sprintf('%.0f', microtime(true) * 1000);
    }

    /**
     * 阻塞等待下一个时间戳
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-01
     * @param int $lastTimestamp
     * @return int
    */
    private function waitTime($lastTimestamp) {
        $timestamp = $this->getTime();
        while ($timestamp <= $lastTimestamp) {
            $cid = \Swoole\Coroutine::getCid();
            if($cid > 0 ){
                \Swoole\Coroutine::sleep(0.001);
            }else{
                usleep(1);
            }
            $timestamp = $this->getTime();
        }
        return $timestamp;
    }
}