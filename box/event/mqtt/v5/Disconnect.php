<?php
/**
 * +----------------------------------------------------------------------
 * MQTT - 服务端 - Dc - 消息事件处理 - 【断开连接时】
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace \box\event\mqtt\v5;

use x\mqtt\base\Event;
use x\mqtt\v5\Dc;
use x\mqtt\common\Types;

class Disconnect extends Event {
    /**
     * 说明：
     * $this->getServer() : 获取Swoole实例
     * $this->getFd() : 获取当前请求标示符
     * $this->getData() : 获取已解码后的数据包
     * $this->getReactorId : 获取当前请求所处的线程ID
    */
    
    /**
     * 事件处理入口
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */ 
    public function run() {

        // 处理完成后需要回复以下内容
        if ($this->getServer()->exist($this->getFd())) {
            $this->getServer()->close($this->getFd());
        }
    }
}