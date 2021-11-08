<?php
/**
 * +----------------------------------------------------------------------
 * 队列驱动-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

abstract class AbstractQueueDriver {
    /**
     * 待确认的队列名称
    */
    protected $_key_confirm = '_q_confirm';
    /**
     * 等待消费的队列名称
    */
    protected $_key_waiting = '_q_wait';
    /**
     * 正在消费的队列名称
    */
    protected $_key_reserved = '_q_reserved';
    /**
     * 延迟消费的队列名称
    */
    protected $_key_delayed = '_q_delayed';
    /**
     * 消费失败的队列名称
    */
    protected $_key_failed = '_q_failed';
    /**
     * 消费超时的队列名称
    */
    protected $_key_timeout = '_q_timeout';
    /**
     * Job序列化实体队列名称
    */
    protected $_key_entity = '_q_entity';
    
    /**
     * 初始化配置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __construct($config) {
        $this->_key_waiting = $config['channel'].$this->_key_waiting;
        $this->_key_reserved = $config['channel'].$this->_key_reserved;
        $this->_key_delayed = $config['channel'].$this->_key_delayed;
        $this->_key_failed = $config['channel'].$this->_key_failed;
        $this->_key_timeout = $config['channel'].$this->_key_timeout;
        $this->_key_entity = $config['channel'].$this->_key_entity;
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
    abstract public function push($Job);

    /**
     * 获取一个任务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.9 + 2021-11-04
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function pop();

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
    abstract public function confirm($Job);

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
    abstract public function JobRetry($Job);

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
    abstract public function JobError($Job);
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
    abstract public function JobOuttime($Job);
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
    abstract public function JobSuccess($Job);
}