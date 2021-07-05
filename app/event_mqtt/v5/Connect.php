<?php
/**
 * +----------------------------------------------------------------------
 * MQTT - 服务端 - Dc - 消息事件处理 - 【建立连接时】
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace app\event_mqtt\v5;

use x\mqtt\base\Event;
use x\mqtt\v5\Dc;
use x\mqtt\common\Types;

class Connect extends Event {
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
        $this->getServer()->send(
            $this->getFd(),
            Dc::pack([
                'type' => Types::CONNACK,
                'code' => 0,
                'session_present' => 0,
                'properties' => [
                    'maximum_packet_size' => 2097152, // 最大数据包大小，默认2M
                    'retain_available' => true, // retain保留消息状态
                    'shared_subscription_available' => true, // 是否支持共享订阅
                    'subscription_identifier_available' => true, // 是否支持订阅标识符
                    'topic_alias_maximum' => 65535, // 最大订阅数
                    'wildcard_subscription_available' => true, // 订阅时是否可以使用通配符主题
                ],
            ])
        );
    }
}