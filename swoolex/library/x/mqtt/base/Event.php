<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 服务端消息事件的抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\base;

abstract class Event {
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
     * 构造函数
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $server
     * @param int $fd 连接的文件描述符
     * @param int $reactorId 连接所在的 Reactor 线程 ID
     * @param string $data 收到的数据内容，已解码
     * @return void
    */
    public function __construct($server, $fd, $reactorId, $data) {
        $this->server = $server;
        $this->fd = $fd;
        $this->reactorId = $reactorId;
        $this->data = $data;
    }

    /**
     * 禁止重写的函数
    */
    protected final function getServer() {
        return $this->server;
    }
    protected final function getFd() {
        return $this->fd;
    }
    protected final function getReactorId() {
        return $this->reactorId;
    }
    protected final function getData() {
        return $this->data;
    }

    /**
     * 挂载控制器
     * @todo 无
     * @author 小黄牛
     * @version v2.0.11 + 2021.07.02
     * @deprecated 暂不启用
     * @global 无
     * @param string $controller
     * @param string $action
     * @param array $param
     * @return Controller
    */
    protected final function controller($controller, $action='index', $param=[]) {
        $class = '\app\mqtt\\'. str_replace('/', '\\', trim($controller, '/'));
        if (!class_exists($class)) {
            throw new \Exception('MQTT '.$class.' not exist');
            return false;
        }

        $obj = new $class;
        if (!method_exists($obj, $action)) {
            throw new \Exception('MQTT '.$class.' action '.$action.' not exist');
            return false;
        }
        $obj->setServer($this->server);
        $obj->setData($this->data);
        $obj->setFd($this->fd);
        $obj->setReactorId($this->reactorId);
        return $obj->$action();
    }

    /**
     * 必须要实现的抽象
    */
    abstract public function run(); // 入口方法
}