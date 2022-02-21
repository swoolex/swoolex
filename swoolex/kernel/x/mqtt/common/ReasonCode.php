<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - 客户端编码
 * +----------------------------------------------------------------------
 * 参考：https://docs.oasis-open.org/mqtt/mqtt/v5.0/os/mqtt-v5.0-os.html#_Toc3901031
 *       https://docs.oasis-open.org/mqtt/mqtt/v5.0/os/mqtt-v5.0-os.html#_Toc3901079
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/simps/mqtt
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common;

class ReasonCode {

    /**
     * 服务端回复客户端十六进制码
    */
    public const SUCCESS = 0x00;
    public const NORMAL_DISCONNECTION = 0x00;
    public const GRANTED_QOS_0 = 0x00;
    public const GRANTED_QOS_1 = 0x01;
    public const GRANTED_QOS_2 = 0x02;
    public const DISCONNECT_WITH_WILL_MESSAGE = 0x04;
    public const NO_MATCHING_SUBSCRIBERS = 0x10;
    public const NO_SUBSCRIPTION_EXISTED = 0x11;
    public const CONTINUE_AUTHENTICATION = 0x18;
    public const RE_AUTHENTICATE = 0x19;
    public const UNSPECIFIED_ERROR = 0x80;
    public const MALFORMED_PACKET = 0x81;
    public const PROTOCOL_ERROR = 0x82;
    public const IMPLEMENTATION_SPECIFIC_ERROR = 0x83;
    public const UNSUPPORTED_PROTOCOL_VERSION = 0x84;
    public const CLIENT_IDENTIFIER_NOT_VALID = 0x85;
    public const BAD_USER_NAME_OR_PASSWORD = 0x86;
    public const NOT_AUTHORIZED = 0x87;
    public const SERVER_UNAVAILABLE = 0x88;
    public const SERVER_BUSY = 0x89;
    public const BANNED = 0x8A;
    public const SERVER_SHUTTING_DOWN = 0x8B;
    public const BAD_AUTHENTICATION_METHOD = 0x8C;
    public const KEEP_ALIVE_TIMEOUT = 0x8D;
    public const SESSION_TAKEN_OVER = 0x8E;
    public const TOPIC_FILTER_INVALID = 0x8F;
    public const TOPIC_NAME_INVALID = 0x90;
    public const PACKET_IDENTIFIER_IN_USE = 0x91;
    public const PACKET_IDENTIFIER_NOT_FOUND = 0x92;
    public const RECEIVE_MAXIMUM_EXCEEDED = 0x93;
    public const TOPIC_ALIAS_INVALID = 0x94;
    public const PACKET_TOO_LARGE = 0x95;
    public const MESSAGE_RATE_TOO_HIGH = 0x96;
    public const QUOTA_EXCEEDED = 0x97;
    public const ADMINISTRATIVE_ACTION = 0x98;
    public const PAYLOAD_FORMAT_INVALID = 0x99;
    public const RETAIN_NOT_SUPPORTED = 0x9A;
    public const QOS_NOT_SUPPORTED = 0x9B;
    public const USE_ANOTHER_SERVER = 0x9C;
    public const SERVER_MOVED = 0x9D;
    public const SHARED_SUBSCRIPTIONS_NOT_SUPPORTED = 0x9E;
    public const CONNECTION_RATE_EXCEEDED = 0x9F;
    public const MAXIMUM_CONNECT_TIME = 0xA0;
    public const SUBSCRIPTION_IDENTIFIERS_NOT_SUPPORTED = 0xA1;
    public const WILDCARD_SUBSCRIPTIONS_NOT_SUPPORTED = 0xA2;
}
