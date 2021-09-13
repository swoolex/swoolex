<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 服务端控制器基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\controller;

class Mqtt {
    /**
     * 服务实例
    */
    protected $server;
    /**
     * 当前连接的FD
    */
    protected $fd;
    /**
     * 当前连接所在的 Reactor 线程 ID
    */
    protected $reactorId;
    /**
     * 已解码的数据包
    */
    protected $data;

    /**
     * 禁止重写的函数
    */
    public final function setServer($server) {
        $this->server = $server;
        return $this;
    }
    public final function setFd($fd) {
        $this->fd = $fd;
        return $this;
    }
    public final function setReactorId($reactorId) {
        $this->reactorId = $reactorId;
        return $this;
    }
    public final function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    protected final function getServer() {
        return $this->server;
    }
    protected final function getFd() {
        return $this->fd;
    }
    protected final function getReactorId() {
        return $this->reactorId;
    }
    public final function getData() {
        return $this->data;
    }
    public final function getLevel() {
        $server = $this->getServer();
        $fd = $this->getFd();
        return $server->fds[$fd];
    }

    /**
     * 获取某个主题下的全部设备信息
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $topic
     * @return array
    */
    protected final function select($topic='/') {
        return (new \x\mqtt\Table($this->server))->getUser($topic);
    }

    /**
     * 指定设备号，读取某个设备的当前信息
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $client_id
     * @return array
    */
    protected final function find($client_id) {
        $array = $this->server->device_list->get($client_id);
        if (!$array) return [];

        $Redis = new \x\Redis();
        $table = new \x\mqtt\Table();
        $hget = $redis->hGetAll($table->hash_key.$client_id);
        $list = array();
        foreach($hget as $key=>$val) {   
            $list[] = [
                'topic' => $key,
                'qos' => $val,
            ];
        }
        $Redis->return();
        unset($table);
        $array['list'] = $list;
        return $array;
    }

    /**
     * 读取当前连接的详情
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    protected final function info() {
        $data = $this->server->device_fd->get($fd);
        if (!$data) return false;

        return $this->find($data['client_id']);
    }

    /**
     * 给某个连接发送消息
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param int $fd
     * @param array $data
     * @return bool
    */
    protected final function send($fd, $data) {
        $arr = $this->getLevel();
        $class = $arr['class'];
        return $this->server->send($fd, $class::pack($data));
    }
}