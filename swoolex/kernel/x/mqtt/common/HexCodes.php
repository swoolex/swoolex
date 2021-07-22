<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 有所相关的16进制编码表 等效二进制
 * +----------------------------------------------------------------------
 * 参考：https://docs.oasis-open.org/mqtt/mqtt/v5.0/os/mqtt-v5.0-os.html#_Toc3901029
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common;

class HexCodes {

    /**
     * 等效：1
    */
    public const PAYLOAD_FORMAT_INDICATOR = 0x01;
    /**
     * 等效：2
    */
    public const MESSAGE_EXPIRY_INTERVAL = 0x02;
    /**
     * 等效：11
    */
    public const CONTENT_TYPE = 0x03;
    /**
     * 等效：1000
    */
    public const RESPONSE_TOPIC = 0x08;
    /**
     * 等效：1001
    */
    public const CORRELATION_DATA = 0x09;
    /**
     * 等效：1011
    */
    public const SUBSCRIPTION_IDENTIFIER = 0x0B;
    /**
     * 等效：10001
    */
    public const SESSION_EXPIRY_INTERVAL = 0x11;
    /**
     * 等效：10010
    */
    public const ASSIGNED_CLIENT_IDENTIFIER = 0x12;
    /**
     * 等效：10011
    */
    public const SERVER_KEEP_ALIVE = 0x13;
    /**
     * 等效：10101
    */
    public const AUTHENTICATION_METHOD = 0x15;
    /**
     * 等效：10110
    */
    public const AUTHENTICATION_DATA = 0x16;
    /**
     * 等效：10111
    */
    public const REQUEST_PROBLEM_INFORMATION = 0x17;
    /**
     * 等效：11000
    */
    public const WILL_DELAY_INTERVAL = 0x18;
    /**
     * 等效：11001
    */
    public const REQUEST_RESPONSE_INFORMATION = 0x19;
    /**
     * 等效：11010
    */
    public const RESPONSE_INFORMATION = 0x1A;
    /**
     * 等效：11100
    */
    public const SERVER_REFERENCE = 0x1C;
    /**
     * 等效：11111
    */
    public const REASON_STRING = 0x1F;
    /**
     * 等效：100001
    */
    public const RECEIVE_MAXIMUM = 0x21;
    /**
     * 等效：100010
    */
    public const TOPIC_ALIAS_MAXIMUM = 0x22;
    /**
     * 等效：100011
    */
    public const TOPIC_ALIAS = 0x23;
    /**
     * 等效：100100
    */
    public const MAXIMUM_QOS = 0x24;
    /**
     * 等效：100101
    */
    public const RETAIN_AVAILABLE = 0x25;
    /**
     * 等效：100110
    */
    public const USER_PROPERTY = 0x26;
    /**
     * 等效：100111
    */
    public const MAXIMUM_PACKET_SIZE = 0x27;
    /**
     * 等效：101000
    */
    public const WILDCARD_SUBSCRIPTION_AVAILABLE = 0x28;
    /**
     * 等效：101001
    */
    public const SUBSCRIPTION_IDENTIFIER_AVAILABLE = 0x29;
    /**
     * 等效：101010
    */
    public const SHARED_SUBSCRIPTION_AVAILABLE = 0x2A;
}