<?php
// +----------------------------------------------------------------------
// | MongoDB配置
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

return [
    // 是否开启连接数监控
    'is_monitor' => true,

    // +----------------------------------------------------------------------
    // | MongoDB连接池配置 key(标识)=>value(Array)
    // +----------------------------------------------------------------------

    // host:端口，集群则参考MongoDb官网文档
    'host' => '127.0.0.1:27017',
    // 用户名
    'user'     => '',
    // 密码
    'password' => '',
    // 库，不选择则默认进入test库
    'database' => '',
    // 连接池数量
    'pool_num' => 0,
    // 空闲连接池检测间隔时间(S)
    'monitor_time' => 1200,
    // 空闲连接回收时间(S)
    'spare_time' => 600,

    // --- 更多参数 - MongoDb相关
    // 同MongoDb文档
    'slaveOk' => false,
    // 同MongoDb文档
    'safe' => false,
    // 同MongoDb文档
    'w' => '',
    // 同MongoDb文档
    'wtimeoutMS' => '',
    // 同MongoDb文档
    'fsync' => false,
    // 同MongoDb文档
    'journal' => false,
    // 连接最长保持时间(0永久)，不建议修改
    'connectTimeoutMS' => 0,
    // 单条命令的最长执行时间(毫秒)
    'socketTimeoutMS' => 5000,

    // --- 更多参数 - 数据包相关 - 具体参考Swoole官方文档Client相关
    // 打开包长检测特性
    'open_length_check'     => true,
    // 长度值的类型
    'package_length_type'   => 'N',
    // 第N个字节是包长度的值
    'package_length_offset' => 0,
    // 第几个字节开始计算长度
    'package_body_offset'   => 4,
    // 协议最大长度
    'package_max_length'    => 2000000,
];
