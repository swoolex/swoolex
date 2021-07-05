<?php
/**
 * +----------------------------------------------------------------------
 * MQTT 用于默认广播的控制器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace app\mqtt\system;
use x\mqtt\Controller;

class index extends Controller {
    
    public function run() {
        // 读取请求内容
        $data = $this->getData();
        // 读取某个主题下的全部设备
        $device_list = $this->select($data['topic']);
        // var_dump($device_list);
        // 获得当前请求的连接标识
        $fd = $this->getFd();
        // 循环广播消息
        foreach ($device_list as $v) {
            // 除了自己都广播
            if ($v['fd'] != $fd) {
                $this->send($v['fd'], [
                        'type' => $data['type'],
                        'topic' => $data['topic'],
                        'message' => $data['message'],
                        'dup' => $data['dup'],
                        'qos' => $data['qos'],
                        'retain' => $data['retain'],
                        'message_id' => $data['message_id'] ?? '',
                    ]
                );
            }
        }
    }
}