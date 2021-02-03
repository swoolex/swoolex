<?php
// +----------------------------------------------------------------------
// | 微服务配置
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

return [
    // 是否启用数据传输加密
    'aes_status'   => true,
    // 数据加密方式
    'aes_method'   => 'AES-128-ECB',
    // 加密密钥
    'aes_key'      => 'swoolex',
    // 加密向量
    'aes_iv'       => '',


    // +-----------------------------
    // | 客户端Client配置
    // +-----------------------------
    
    // rpc请求超时时间(s)
    'out_time' => 5,
    // SWOOLE-CLIENT 第N个字节是包长度的值
    'package_length_offset' => 0, 
    // SWOOLE-CLIENT 第几个字节开始计算长度
    'package_body_offset'   => 4,
    // SWOOLE-CLIENT 协议最大长度
    'package_max_length'    => 2000000, 
    // SWOOLE-CLIENT 总超时，包括连接、发送、接收所有超时
    'timeout' => 0.5, 
    // SWOOLE-CLIENT 连接超时
    'connect_timeout' => 1.0,
    // SWOOLE-CLIENT 接收超时 
    'write_timeout' => 10.0,
    // SWOOLE-CLIENT 发送超时 
    'read_timeout' => 0.5, 
    
    // +-----------------------------
    // | Redis 服务的Key
    // +-----------------------------
    'redis_key' => 'swoolex_rpc',
    // 单个服务最大主动错误记录数
    'rpc_error_max' => 100,
    
    // +-----------------------------
    // | HTTP-RPC服务中心相关
    // +-----------------------------
    // 当前应用是否为服务中心
    'http_rpc_is'       => false,
    // 控制台账号
    'http_rpc_username' => 'swoolex',
    // 控制台密码
    'http_rpc_password' => 'swoolex',
];
