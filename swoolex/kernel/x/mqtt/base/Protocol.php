<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 协议基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/


namespace x\mqtt\base;

interface Protocol {
    /**
     * 协议版本，对应3.1
    */
    public const MQTT_PROTOCOL_LEVEL_3_1 = 3;
    /**
     * 协议版本，对应3.1.1
    */
    public const MQTT_PROTOCOL_LEVEL_3_1_1 = 4;
    /**
     * 协议版本，对应5.0
    */
    public const MQTT_PROTOCOL_LEVEL_5_0 = 5;
    /**
     * 协议名称
    */
    public const MQISDP_PROTOCOL_NAME = 'MQIsdp';
    /**
     * 协议名称
    */
    public const MQTT_PROTOCOL_NAME = 'MQTT';
    /**
     * QOS_0 的标示
    */
    public const MQTT_QOS_0 = 0;
    /**
     * QOS_1 的标示
    */
    public const MQTT_QOS_1 = 1;
    /**
     * QOS_2 的标示
    */
    public const MQTT_QOS_2 = 2;
    /**
     * RETAIN_0 的标示
    */
    public const MQTT_RETAIN_0 = 0;
    /**
     * RETAIN_1 的标示
    */
    public const MQTT_RETAIN_1 = 1;
    /**
     * RETAIN_2 的标示
    */
    public const MQTT_RETAIN_2 = 2;
    /**
     * DUP_0 的标示
    */
    public const MQTT_DUP_0 = 0;
    /**
     * DUP_1 的标示
    */
    public const MQTT_DUP_1 = 1;
    /**
     * SESSION_PRESENT_0 的标示
    */
    public const MQTT_SESSION_PRESENT_0 = 0;
    /**
     * SESSION_PRESENT_1 的标示
    */
    public const MQTT_SESSION_PRESENT_1 = 1;

    /**
     * 必须实现的接口
    */
    public static function pack($array);
    public static function unpack($data);
}
