<?php
// +----------------------------------------------------------------------
// | 初始化
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

// 框架当前版本
define('VERSION', 'v1.2.31');
// 项目根地址
define('ROOT_PATH', dirname(__DIR__));
// 缓存 && 日志根地址
define('RUNTIME_PATH', ROOT_PATH.'/runtime/');

// 载入Loader类
require_once __DIR__.'/library/x/Loader.php';

// 注册自动加载
Loader::register();

// 引入系统助手函数
require_once __DIR__.'/helper.php';
// 引入应用函数
require_once ROOT_PATH.'/common/common.php';

// 注册错误和异常处理机制
Error::run()->register();

// 配置文件加载
Config::run();

// 日志模块初始化
Log::run()->start();
