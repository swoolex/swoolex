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

namespace event\mqtt;
use x\mqtt\common\Types;
use x\Config;

class onReceive {
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
            $ip = $server->getClientInfo($fd)['remote_ip'];
            // 触发限流器
            if (\x\Limit::ipVif($server, $fd, $ip, 'mqtt') == false) {
                return false;
            }
            
            // 上下文管理
            \x\context\Container::set('server', $server);
            \x\context\Container::set('fd', $fd);
            
            $this->server = $server;
            
            // 业务挂载
            $this->mqtt($server, $fd, $reactorId, $data);

            \x\context\Container::delete();

            // 调用二次转发，不做重载
            $on = new \box\event\server\onReceive;
            $on->run($server, $fd, $reactorId, $data);
            
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
        $type = \x\mqtt\common\tool\UnPack::getType($data);
        // 建立连接时
        if ($type === Types::CONNECT) {
            $level = \x\mqtt\common\tool\UnPack::getLevel($data);
            if ($level === 5) {
                $class = '\x\mqtt\v5\Dc';
            } else {
                $level = 3;
                $class = '\x\mqtt\v3\Dc';
            }
            
            $data = $class::unpack($data);
            if ($data['protocol_name'] != 'MQTT') {
                $server->close($fd);
                return false;
            }
            // 账号密码校验
            if (Config::get('mqtt.auth_status') == false) {
                if ($data['user_name'] != Config::get('mqtt.user_name') || $data['password'] != Config::get('mqtt.password')) {
                    $server->close($fd);
                    return false;
                }
            } else {
                // 启用密码器
                $MqttCipherClass = Config::get('mqtt.cipher');
                $MqttCipher = (new $MqttCipherClass())->run($data);
                if ($MqttCipher == false) {
                    $server->close($fd);
                    return false;
                }
            }
            
            (new \x\mqtt\Table($server))->deviceReload($data, $fd, $level);
            (new \box\event\mqtt\Connect($server, $fd, $reactorId, $data))->run();
        // 其他协议
        } else {
            $level = (new \x\mqtt\Table($this->server))->deviceLevel($fd);
            if ($level === 5) {
                $class = '\x\mqtt\v5\Dc';
            } else {
                $class = '\x\mqtt\v3\Dc';
            }
            $data = $class::unpack($data);
            // 数据包解析成功
            if (is_array($data) && isset($data['type'])) {
                // 记录日志
                if (Config::get('app.de_bug') == true) {
                    \x\Log::setMqtt(date('Y-m-d H:i:s', time()).' FD：'.$fd.'，数据包：'.json_encode($data, JSON_UNESCAPED_UNICODE).PHP_EOL);
                }
                // 不同的消息类型
                switch ($data['type']) {
                    // 心跳请求
                    case Types::PINGREQ:
                        (new \x\mqtt\Table($server))->devicePing($fd);
                        (new \box\event\mqtt\Pingreq($server, $fd, $reactorId, $data))->run();
                    break;
                    // 断开连接
                    case Types::DISCONNECT:
                        (new \x\mqtt\Table($server))->deviceDelete($fd);
                        (new \box\event\mqtt\Disconnect($server, $fd, $reactorId, $data))->run();
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
                        (new \box\event\mqtt\Publish($server, $fd, $reactorId, $data))->run();
                    break;
                    // 订阅主题
                    case Types::SUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicReload($data['topics'], $fd);
                        (new \box\event\mqtt\Subscribe($server, $fd, $reactorId, $data))->run();
                    break;
                    // 取消订阅
                    case Types::UNSUBSCRIBE:
                        (new \x\mqtt\Table($server))->topicDelete(current($data['topics']), $fd);
                        (new \box\event\mqtt\UnSubscribe($server, $fd, $reactorId, $data))->run();
                    break;
                }
            } else {
                $server->close($fd);
            }
        }
    }
}

