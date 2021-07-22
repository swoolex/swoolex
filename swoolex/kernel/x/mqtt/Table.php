<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 内存表维护
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt;

class Table {
    /**
     * Swoole-Server实例
    */
    private $Server;
    /**
     * Redis主题-SETS的key集合，代替keys(*)
    */
    private $mqtt_key = 'mqtt_sets';
    /**
     * Redis主题-SETS
    */
    private $sets_key = 'mqtt_sets_';
    /**
     * Redis主题-INCR
    */
    private $incr_key = 'mqtt_incr_';
    /**
     * Redis主题-HASH
    */
    public $hash_key = 'mqtt_hash_';

    /**
     * 植入SW实例
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param Server $server
     * @return void
    */
    public function __construct($server=null) {
        $this->Server = $server;
    }

    /**
     * 设备号更新
     * Connect阶段调用
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param array $data 数据包
     * @param int $fd
     * @return bool
    */
    public function deviceReload($data, $fd) {
        $res = $this->Server->device_list->set($data['client_id'], [
            'fd' => $fd,
            'status' => 1, // 在线
            'client_id' => $data['client_id'],
            'ping_time' => time(),
        ]);

        if (!$res) return false;

        return $this->Server->device_fd->set($fd, [
            'client_id' => $data['client_id'],
        ]);
    }

    /**
     * 设备断开连接
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param int $fd
     * @return bool
    */
    public function deviceDelete($fd) {
        $data = $this->Server->device_fd->get($fd);
        if (!$data) return false;

        $res = $this->Server->device_fd->del($fd);
        if (!$res) return false;

        return $this->Server->device_list->set($data['client_id'], [
            'status' => 2, // 离线
        ]);
    }

    /**
     * 设备心态更新
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param int $fd
     * @return bool
    */
    public function devicePing($fd) {
        $data = $this->Server->device_fd->get($fd);
        if (!$data) return false;

        return $this->Server->device_list->set($data['client_id'], [
            'ping_time' => time(), // 更新心跳时间
        ]);
    }
    
    /**
     * 获取全部设备
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @return bool
    */
    public function deviceList() {
        $list = [];
        foreach ($this->Server->device_list as $v) {
            $list[] = $v;
        }
        return $list;
    }

    /**
     * 获取某个设备的当前FD标识
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $client_id
     * @return int
    */
    public function deviceFd($client_id) {
        $arr = $this->Server->device_list->get($client_id);
        if (!$arr) return false;
        return $arr['fd'];
    }

    /**
     * 消息订阅时更新
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 主题
     * @param int $fd
     * @return bool
    */
    public function topicReload($data, $fd) {
        $info = $this->Server->device_fd->get($fd);
        if (!$info) return false;
        $topic = key($data);
        $qos = $data[$topic]['qos'];

        $Redis = new \x\Redis();
        $res = $Redis->SISMEMBER($this->sets_key.$topic, $info['client_id']);
        if (!$res) {
            $res = $Redis->SISMEMBER($this->mqtt_key, $topic);
            if (!$res) {
                $Redis->SADD($this->mqtt_key, $topic);
            }
            $Redis->SADD($this->sets_key.$topic, $info['client_id']);
            $Redis->INCR($this->incr_key.$topic, 1);
            // 直接更新hash关联
            $Redis->HSET($this->hash_key.$info['client_id'], $topic, $qos);
        }
        $Redis->return();

        return true;
    }

    /**
     * 取消订阅时删除
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $topic 主题
     * @param int $fd
     * @return bool
    */
    public function topicDelete($topic, $fd) {
        $data = $this->Server->device_fd->get($fd);
        if (!$data) return false;
        $Redis = new \x\Redis();
        $res = $Redis->SISMEMBER($this->sets_key.$topic, $data['client_id']);
        if ($res) {
            $res = $Redis->SISMEMBER($this->mqtt_key, $topic);
            if ($res) {
                $Redis->SREM($this->mqtt_key, $topic);
            }
            $Redis->SREM($this->sets_key.$topic, $data['client_id']);
            $Redis->DECR($this->incr_key.$topic, 1);
            // 读取key
            $num = $Redis->get($this->incr_key.$topic);
            if ($num <= 0) {
                $Redis->del($this->sets_key.$topic);
                $Redis->del($this->incr_key.$topic);
            }
            // 删除hash关联
            $Redis->HDEL($this->hash_key.$data['client_id'], $topic);
        }
        $Redis->return();

        return true;
    }

    /**
     * 获取某个主题下的订阅信息
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $topic
     * @return void
    */
    public function getUser($topic) {
        if ($topic != '/') $topic = ltrim($topic, '/');
        $log_list = [];
        $return_data = [];
        $Redis = new \x\Redis();
        // 带通配符
        if (strpos($topic, '#') !== false || strpos($topic, '+') !== false) {
            $keys = $Redis->SMEMBERS($this->mqtt_key);
            if ($keys) {
                foreach ($keys as $topic2) {
                    if (strpos($topic2, $topic) !== false) {
                        $list = $Redis->SMEMBERS($this->sets_key.$topic2);
                        if ($list) {
                            // 拿到设备ID
                            foreach ($list as $client_id) {
                                $key = $client_id.$topic2;
                                if (!isset($log_list[$key])) {
                                    $qos = $Redis->HGET($this->hash_key.$client_id, $topic2);
                                    $return_data[] = [
                                        'fd' => $this->deviceFd($client_id),
                                        'client_id' => $client_id,
                                        'topic' => $topic2,
                                        'qos' => $qos,
                                    ];
                                    $log_list[$key] = 1;
                                }
                            }
                        }
                    }
                }
            }
        // 全部主题
        } else if ($topic == '/') {
            $keys = $Redis->SMEMBERS($this->mqtt_key);
            if ($keys) {
                foreach ($keys as $topic) {
                    $list = $Redis->SMEMBERS($this->sets_key.$topic);
                    if ($list) {
                        // 拿到设备ID
                        foreach ($list as $client_id) {
                            $key = $client_id.$topic;
                            if (!isset($log_list[$key])) {
                                $qos = $Redis->HGET($this->hash_key.$client_id, $topic);
                                $return_data[] = [
                                    'fd' => $this->deviceFd($client_id),
                                    'client_id' => $client_id,
                                    'topic' => $topic,
                                    'qos' => $qos,
                                ];
                                $log_list[$key] = 1;
                            }
                        }
                    }
                }
            }
        // 分解
        } else {
            // 先查出直接的
            $list = $Redis->SMEMBERS($this->sets_key.$topic);
            if ($list) {
                // 拿到设备ID
                foreach ($list as $client_id) {
                    $key = $client_id.$topic;
                    if (!isset($log_list[$key])) {
                        $qos = $Redis->HGET($this->hash_key.$client_id, $topic);
                        $return_data[] = [
                            'fd' => $this->deviceFd($client_id),
                            'client_id' => $client_id,
                            'topic' => $topic,
                            'qos' => $qos,
                        ];
                        $log_list[$key] = 1;
                    }
                }
            }

            while ($topic = dirname($topic)) {
                if ($topic == '.') break;

                $topic2 = $topic.'/#';
                $list = $Redis->SMEMBERS($this->sets_key.$topic2);
                if ($list) {
                    // 拿到设备ID
                    foreach ($list as $client_id) {
                        $key = $client_id.$topic2;
                        if (!isset($log_list[$key])) {
                            $qos = $Redis->HGET($this->hash_key.$client_id, $topic2);
                            $return_data[] = [
                                'fd' => $this->deviceFd($client_id),
                                'client_id' => $client_id,
                                'topic' => $topic2,
                                'qos' => $qos,
                            ];
                            $log_list[$key] = 1;
                        }
                    }
                }

                $topic2 = $topic.'/+';
                $list = $Redis->SMEMBERS($this->sets_key.$topic2);
                if ($list) {
                    // 拿到设备ID
                    foreach ($list as $client_id) {
                        $key = $client_id.$topic2;
                        if (!isset($log_list[$key])) {
                            $qos = $Redis->HGET($this->hash_key.$client_id, $topic2);
                            $return_data[] = [
                                'fd' => $this->deviceFd($client_id),
                                'client_id' => $client_id,
                                'topic' => $topic2,
                                'qos' => $qos,
                            ];
                            $log_list[$key] = 1;
                        }
                    }
                }
            }
        }
        unset($log_list);

        $Redis->return();
        return $return_data;
    }
}