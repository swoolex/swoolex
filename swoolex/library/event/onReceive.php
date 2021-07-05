<?php
/**
 * +----------------------------------------------------------------------
 * 接收到数据时
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace event;

use x\mqtt\common\Types;
use x\Config;

class onReceive
{
    /**
	 * 启动实例
	*/
    public $server;

    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.3 + 2020.07.06
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，可能是文本或者二进制内容
     * @return void
    */
    public function run($server, $fd, $reactorId, $data=null) {
        try {
            $this->server = $server;
            
            // 微服务
            if (Config::get('server.sw_service_type') == 'rpc') {
                $this->rpc($server, $fd, $reactorId, $data);
            // MQTT
            } else if (Config::get('server.sw_service_type') == 'mqtt') {
                $this->mqtt($server, $fd, $reactorId, $data);
            // 其他
            } else {
                $this->server($server, $fd, $reactorId, $data);
            }
        } catch (\Throwable $throwable) {
            return \x\Error::run()->halt($throwable);
        }
    }

    /**
     * 物联网MQTT服务
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function mqtt($server, $fd, $reactorId, $data) {
        // 协议版本判断
        $edition = 'v'.Config::get('mqtt.protocol_level');
        if ($edition == 'v5') {
            $data = \x\mqtt\v5\Dc::unpack($data);
        } else {
            $data = \x\mqtt\v3\Dc::unpack($data);
        }
        
        // 数据包解析成功
        if (is_array($data) && isset($data['type'])) {
            if ($edition == 'v5') {
                // 不同的消息类型
                switch ($data['type']) {
                    // 建立连接
                    case Types::CONNECT:
                        if ($data['protocol_name'] != 'MQTT') {
                            $server->close($fd);
                            return false;
                        }
                        // 账号密码校验
                        if ($data['user_name'] != Config::get('mqtt.user_name') || $data['password'] != Config::get('mqtt.password')) {
                            $server->close($fd);
                            return false;
                        }
                        (new \x\mqtt\Table($server))->deviceReload($data, $fd);
                        (new \app\event_mqtt\v5\Connect($server, $fd, $reactorId, $data))->run();
                    break;
                    // 心跳请求
                    case Types::PINGREQ:
                        (new \x\mqtt\Table($server))->devicePing($fd);
                        (new \app\event_mqtt\v5\Pingreq($server, $fd, $reactorId, $data))->run();
                    break;
                    // 断开连接
                    case Types::DISCONNECT:
                        (new \x\mqtt\Table($server))->deviceDelete($fd);
                        (new \app\event_mqtt\v5\Disconnect($server, $fd, $reactorId, $data))->run();
                    break;
                    // 发布消息
                    case Types::PUBLISH:
                        if (Config::get('mqtt.publish_wildcard_status') == false) {
                            // 非法设备，直接断开
                            if (strpos($data['topic'], '#') !== false || strpos($data['topic'], '+') !== false) {
                                $server->send(
                                    $fd,
                                    Dc::pack([
                                        'type' => Types::DISCONNECT,
                                        'message_id' => $data['message_id'] ?? '',
                                    ])
                                );
                                break;
                            }
                        }
                        (new \app\event_mqtt\v5\Publish($server, $fd, $reactorId, $data))->run();
                    break;
                    // 订阅主题
                    case Types::SUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicReload($data['topics'], $fd);
                        (new \app\event_mqtt\v5\Subscribe($server, $fd, $reactorId, $data))->run();
                    break;
                    // 取消订阅
                    case Types::UNSUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicDelete(current($data['topics']), $fd);
                        (new \app\event_mqtt\v5\UnSubscribe($server, $fd, $reactorId, $data))->run();
                    break;
                }
            } else {
                // 不同的消息类型
                switch ($data['type']) {
                    // 建立连接
                    case Types::CONNECT:
                        if ($data['protocol_name'] != 'MQTT') {
                            $server->close($fd);
                            return false;
                        }
                        // 账号密码校验
                        if ($data['user_name'] != Config::get('mqtt.user_name') || $data['password'] != Config::get('mqtt.password')) {
                            $server->close($fd);
                            return false;
                        }
                        (new \x\mqtt\Table($server))->deviceReload($data, $fd);
                        (new \app\event_mqtt\v3\Connect($server, $fd, $reactorId, $data))->run();
                    break;
                    // 心跳请求
                    case Types::PINGREQ:
                        (new \x\mqtt\Table($server))->devicePing($fd);
                        (new \app\event_mqtt\v3\Pingreq($server, $fd, $reactorId, $data))->run();
                    break;
                    // 断开连接
                    case Types::DISCONNECT:
                        (new \x\mqtt\Table($server))->deviceDelete($fd);
                        (new \app\event_mqtt\v3\Disconnect($server, $fd, $reactorId, $data))->run();
                    break;
                    // 发布消息
                    case Types::PUBLISH:
                        if (Config::get('mqtt.publish_wildcard_status') == false) {
                            // 非法设备，直接断开
                            if (strpos($data['topic'], '#') !== false || strpos($data['topic'], '+') !== false) {
                                $server->send(
                                    $fd,
                                    Dc::pack([
                                        'type' => Types::DISCONNECT,
                                        'message_id' => $data['message_id'] ?? '',
                                    ])
                                );
                                break;
                            }
                        }
                        (new \app\event_mqtt\v3\Publish($server, $fd, $reactorId, $data))->run();
                    break;
                    // 订阅主题
                    case Types::SUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicReload($data['topics'], $fd);
                        (new \app\event_mqtt\v3\Subscribe($server, $fd, $reactorId, $data))->run();
                    break;
                    // 取消订阅
                    case Types::UNSUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicDelete(current($data['topics']), $fd);
                        (new \app\event_mqtt\v3\UnSubscribe($server, $fd, $reactorId, $data))->run();
                    break;
                }
            }
        } else {
            $server->close($fd);
        }
    }

    /**
     * 微服务TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function rpc($server, $fd, $reactorId, $data) {
        // 请求注入容器
        \x\Container::set('server', $server);
        \x\Container::set('reactorId', $reactorId);
        // 数据解密
        if (Config::get('rpc.aes_status') == true) {
            $Currency = new \x\rpc\Currency();
            $data = $Currency->aes_decrypt($data);
            unset($Currency);
        }
        $data = json_decode($data, true);

        if (isset($data['task'])) {
            if ($data['task'] == true) {
                // 投递异步任务
                $data['swoolex_rpc_task'] = 1;
                $task_id = $server->task(json_encode($data, JSON_UNESCAPED_UNICODE));
                // 直接返回结果
                $ServerCurrency = new \x\rpc\ServerCurrency();
                $ret = $ServerCurrency->returnJson($server, $fd, '200', 'SUCCESS', true);
                // 销毁整个请求级容器
                \x\Container::clear();
                return $ret;
            }
        }

        # 开始转发路由
        $obj = new \x\rpc\ServerRoute();
        $obj->start($server, $fd, $reactorId, $data);

        // 销毁整个请求级容器
        \x\Container::clear();
    }

    /**
     * 普通TCP服务
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function server($server, $fd, $reactorId, $data) {
        // 调用二次转发，不做重载
        $on = new \app\event\onReceive;
        $on->run($server, $fd, $reactorId, $data);
    }
}

