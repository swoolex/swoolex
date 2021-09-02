<?php
/**
 * +----------------------------------------------------------------------
 * 框架常量定义
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

// 框架当前版本
define('VERSION', 'v2.5.5');
// 目录分割符
define('DS', DIRECTORY_SEPARATOR);
// 框架核心包目录
define('SWOOLEX_PATH', dirname(__DIR__).DS);
// 项目根目录
define('ROOT_PATH', dirname(SWOOLEX_PATH).DS);
// 应用根目录
define('APP_PATH', ROOT_PATH.'app'.DS);
// 自定义扩展包根目录
define('EXTEND_PATH', ROOT_PATH.'extend'.DS);
// 框架箱子根目录
define('BOX_PATH', ROOT_PATH.'box'.DS);
// 缓存 && 日志根地址
define('WORKLOG_PATH', ROOT_PATH.'worklog'.DS);
// 初始化配置根目录
define('RUN_PATH', SWOOLEX_PATH.'run'.DS);
// 框架静态资源目录
define('EXAMPLES_PATH', SWOOLEX_PATH.'examples'.DS);
// 开箱及系统CMD命令所需静态资源目录
define('BUILT_PATH', EXAMPLES_PATH.'built'.DS);
// 核心组件目录
define('KERNEL_PATH', SWOOLEX_PATH.'kernel'.DS);
// 组件抽象类目录
define('DESIGN_PATH', KERNEL_PATH.'design'.DS);
// Swoole消息事件根目录
define('EVENT_PATH', KERNEL_PATH.'event'.DS);
// 组件实体类存放目录
define('X_PATH', KERNEL_PATH.'x'.DS);