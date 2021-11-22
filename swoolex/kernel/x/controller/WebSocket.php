<?php
/**
 * +----------------------------------------------------------------------
 * WebSocket控制器基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\controller;

class WebSocket {

    /**
     * 利用析构函数，自动回收归还连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __destruct() {
        $list = get_object_vars($this);
        foreach ($list as $name=>$value) {
            if (is_object($value)) {
                $obj = get_class($value);
                if ($obj == 'x\\Db' || $obj == 'x\\Redis') {
                    $this->$name->return();
                }
            }
        }
    }

    /** 
     * AES加密方法，对数据进行加密，返回加密后的数据 
     * @param string $data 要加密的数据 
     * @return string 
     */  
    private static function encrypt($data) {  
        $config = \x\Config::get('server');
        return openssl_encrypt($data, $config['aes_type'], $config['aes_key'], 0, $config['aes_iv']);  
    }  
  
    /** 
     * AES解密方法，对数据进行解密，返回解密后的数据 
     * @param string $data 要解密的数据 
     * @return string 
     */  
    private static function decrypt($data) {  
        $config = \x\Config::get('server');
        return openssl_decrypt($data, $config['aes_type'], $config['aes_key'], 0, $config['aes_iv']);  
    } 

    /**
     * 获取参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.1.13
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function param() {
        $data = $this->get_data();
        return $data['data'];
    }

    /**
     * 获取参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.1.13
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function get_data() {
        $websocket_frame = \x\context\Container::get('websocket_frame');
        $data = $websocket_frame->data;
        $config = \x\Config::get('server');
        // 启用加密方式
        if ($config['aes_key']) {
            $data = self::decrypt($data);
        }

        return json_decode($data, true);
    }

    /**
     * 更新参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.1.13
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public final function set_data($data) {
        $config = \x\Config::get('server');
        // 启用加密方式
        if ($config['aes_key']) {
            $data = self::encrypt(json_encode($data, JSON_UNESCAPED_UNICODE));
        }

        $websocket_frame = \x\context\Container::get('websocket_frame');
        $websocket_frame->data = $data;
        
        return \x\context\Container::set('websocket_frame', $websocket_frame);
    }

    /**
     * 获取当前客户端fd标识
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-02
     * @deprecated 暂不启用
     * @global 无
     * @return int
    */
    public final function get_current_fd() {
        $websocket_frame = \x\context\Container::get('websocket_frame');
        return $websocket_frame->fd;
    }

    /**
     * 发送数据包
     * @todo 无
     * @author 小黄牛
     * @version v1.0.13 + 2020.05.04
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole $ws 实例
     * @param string $action 回调事件
     * @param string $msg 说明
     * @param mixed $data 结果集
     * @param string $fd 推送标记
     * @param string $websocket_server websocket的连接实例，用于在定时器、sw事件中传入
     * @return void
    */
    public final function fetch($action, $msg='success', $data=[], $fd=null, $websocket_server=null) {
        if (!$websocket_server) $websocket_server = \x\context\Container::get('websocket_server');

        if (!$fd) {
            $websocket_frame = \x\context\Container::get('websocket_frame');
            $fd = $websocket_frame->fd;
        }
        $config = \x\Config::get('server');
        $array = [
            'action' => $action,
            'msg' => $msg,
            'data' => $data
        ];
        $content = json_encode($array, JSON_UNESCAPED_UNICODE);

        // 启用加密方式
        if ($config['aes_key']) {
            $content = self::encrypt($content);
        }

        try {
            $ret = $websocket_server->push($fd, $content);
            return $ret;
        } catch (\Exception $e) {
            // 生命周期回调
            return \design\Lifecycle::websocket_push_error($websocket_server, $content, $fd);
        }
    } 

}