<?php
/**
 * +----------------------------------------------------------------------
 * MQTT - 服务端 - Dc - 消息事件处理 - 【订阅主题时】
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\event\mqtt;

use x\mqtt\base\Event;
use x\mqtt\common\Types;
use x\mqtt\common\ReasonCode;

class Subscribe extends Event {
    /**
     * 说明：
     * $this->getServer() : 获取Swoole实例
     * $this->getFd() : 获取当前请求标示符
     * $this->getData() : 获取已解码后的数据包
     * $this->getReactorId : 获取当前请求所处的线程ID
     * $this->getLevel : 获得协议类型信息
    */
    
    /**
     * 事件处理入口
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
    */ 
    public function run() {
        // 获得协议处理类
        $arr = $this->getLevel();
        $class = $arr['class'];

        $data = $this->getData();

        // 处理完成后需要回复以下内容
        $ret = [
            'type' => Types::SUBACK,
            'message_id' => $data['message_id'] ?? '',
        ];
        $payload = [];
        if ($arr['level'] == 5) {
            foreach ($data['topics'] as $k => $option) {
                $qos = $option['qos'];
                if (is_numeric($qos) && $qos < 3) {
                    $payload[] = $qos;
                } else {
                    $payload[] = ReasonCode::QOS_NOT_SUPPORTED;
                }
            }
        } else {
            foreach ($data['topics'] as $k => $qos) {
                if (is_numeric($qos) && $qos < 3) {
                    $payload[] = $qos;
                } else {
                    $payload[] = 0x80;
                }
            }
        }
        $ret['codes'] = $payload;
        
        $this->getServer()->send(
            $this->getFd(),
            $class::pack($ret)
        );
    }
}