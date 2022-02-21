<?php
/**
 * +----------------------------------------------------------------------
 * MQTT服务 - V5 - 数据包字段名称大全
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/simps/mqtt
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mqtt\common\dc;

use x\mqtt\common\HexCodes;

class PacketFields {
    public static $connect = [
        HexCodes::SESSION_EXPIRY_INTERVAL => 'session_expiry_interval',
        HexCodes::AUTHENTICATION_METHOD => 'authentication_method',
        HexCodes::AUTHENTICATION_DATA => 'authentication_data',
        HexCodes::REQUEST_PROBLEM_INFORMATION => 'request_problem_information',
        HexCodes::REQUEST_RESPONSE_INFORMATION => 'request_response_information',
        HexCodes::RECEIVE_MAXIMUM => 'receive_maximum',
        HexCodes::TOPIC_ALIAS_MAXIMUM => 'topic_alias_maximum',
        HexCodes::USER_PROPERTY => 'user_property',
        HexCodes::MAXIMUM_PACKET_SIZE => 'maximum_packet_size',
    ];

    public static $connAck = [
        HexCodes::SESSION_EXPIRY_INTERVAL => 'session_expiry_interval',
        HexCodes::ASSIGNED_CLIENT_IDENTIFIER => 'assigned_client_identifier',
        HexCodes::SERVER_KEEP_ALIVE => 'server_keep_alive',
        HexCodes::AUTHENTICATION_METHOD => 'authentication_method',
        HexCodes::AUTHENTICATION_DATA => 'authentication_data',
        HexCodes::RESPONSE_INFORMATION => 'response_information',
        HexCodes::SERVER_REFERENCE => 'server_reference',
        HexCodes::REASON_STRING => 'reason_string',
        HexCodes::RECEIVE_MAXIMUM => 'receive_maximum',
        HexCodes::TOPIC_ALIAS_MAXIMUM => 'topic_alias_maximum',
        HexCodes::MAXIMUM_QOS => 'maximum_qos',
        HexCodes::RETAIN_AVAILABLE => 'retain_available',
        HexCodes::USER_PROPERTY => 'user_property',
        HexCodes::MAXIMUM_PACKET_SIZE => 'maximum_packet_size',
        HexCodes::WILDCARD_SUBSCRIPTION_AVAILABLE => 'wildcard_subscription_available',
        HexCodes::SUBSCRIPTION_IDENTIFIER_AVAILABLE => 'subscription_identifier_available',
        HexCodes::SHARED_SUBSCRIPTION_AVAILABLE => 'shared_subscription_available',
    ];

    public static $publish = [
        HexCodes::PAYLOAD_FORMAT_INDICATOR => 'payload_format_indicator',
        HexCodes::MESSAGE_EXPIRY_INTERVAL => 'message_expiry_interval',
        HexCodes::CONTENT_TYPE => 'content_type',
        HexCodes::RESPONSE_TOPIC => 'response_topic',
        HexCodes::CORRELATION_DATA => 'correlation_data',
        HexCodes::SUBSCRIPTION_IDENTIFIER => 'subscription_identifier',
        HexCodes::TOPIC_ALIAS => 'topic_alias',
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $pubAndSub = [
        HexCodes::REASON_STRING => 'reason_string',
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $subscribe = [
        HexCodes::SUBSCRIPTION_IDENTIFIER => 'subscription_identifier',
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $unSubscribe = [
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $disConnect = [
        HexCodes::SESSION_EXPIRY_INTERVAL => 'session_expiry_interval',
        HexCodes::SERVER_REFERENCE => 'server_reference',
        HexCodes::REASON_STRING => 'reason_string',
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $auth = [
        HexCodes::AUTHENTICATION_METHOD => 'authentication_method',
        HexCodes::AUTHENTICATION_DATA => 'authentication_data',
        HexCodes::REASON_STRING => 'reason_string',
        HexCodes::USER_PROPERTY => 'user_property',
    ];

    public static $willProperties = [
        HexCodes::PAYLOAD_FORMAT_INDICATOR => 'payload_format_indicator',
        HexCodes::MESSAGE_EXPIRY_INTERVAL => 'message_expiry_interval',
        HexCodes::CONTENT_TYPE => 'content_type',
        HexCodes::RESPONSE_TOPIC => 'response_topic',
        HexCodes::CORRELATION_DATA => 'correlation_data',
        HexCodes::WILL_DELAY_INTERVAL => 'will_delay_interval',
        HexCodes::USER_PROPERTY => 'user_property',
    ];
}
