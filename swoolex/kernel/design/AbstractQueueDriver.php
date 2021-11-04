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
     * @param string $outtime 获取超时时间
     * @return void
    */
    abstract public function pop($outtime);

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
}