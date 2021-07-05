<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 消息类型
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common;

class Types {
    /**
     * 客户端请求连接到服务器：双方握手
    */
    public const CONNECT = 1;
    /**
     * 连接确认：向客户端确认允许连接
    */
    public const CONNACK = 2;
    /**
     * 发布消息：向客户端或服务端广播消息
    */
    public const PUBLISH = 3;
    /**
     * 发布确认：
    */
    public const PUBACK = 4;
    /**
     * 发布消息收到
    */
    public const PUBREC = 5;
    /**
     * 发布消息释放
    */
    public const PUBREL = 6;
    /**
     * 发布消息完成
    */
    public const PUBCOMP = 7;
    /**
     * 订阅主题
    */
    public const SUBSCRIBE = 8;
    /**
     * 订阅确认
    */
    public const SUBACK = 9;
    /**
     * 取消订阅
    */
    public const UNSUBSCRIBE = 10;
    /**
     * 取消订阅确认
    */
    public const UNSUBACK = 11;
    /**
     * 心跳请求
    */
    public const PINGREQ = 12;
    /**
     * 心跳响应
    */
    public const PINGRESP = 13;
    /**
     * 断开连接
    */
    public const DISCONNECT = 14;
    /**
     * 身份信息交验
    */
    public const AUTH = 15;
}
