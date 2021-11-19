<?php
/**
 * +----------------------------------------------------------------------
 * 框架初始化
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
// 载入常量配置文件
require_once __DIR__.'/constant.php';
// 载入Loader类
require_once X_PATH.'Loader.php';
// 注册自动加载
\x\Loader::register();
// 引入系统助手函数
require_once RUN_PATH.'system.php';
// 引入应用函数
require_once ROOT_PATH.'common'.DS.'common.php';
// 配置文件加载
\x\Config::start();
// // 注册错误和异常处理机制
\x\Error::run()->register();
// 日志模块初始化
\x\Log::start();
// 全局计数器初始化
\x\swoole\Atomic::run();
// 路由表初始化
\x\Route::run();
// 限流器初始化
\x\Limit::run();
// 中间件初始化
\x\middleware\Loader::run()->init();
// 服务启动
\x\App::run()->start();