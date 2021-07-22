<?php
/**
 * +----------------------------------------------------------------------
 * 系统提示语包
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

class SystemTips {
    const CMD_SERVER_MISSING_1 = '指令错误：缺少更多的命令参数';
    const CMD_SERVER_MISSING_2 = '指令错误：sw-x start 后面的服务类型错误，我们只支持：';
    const CMD_SERVER_MISSING_3 = '指令错误：sw-x start [server] 开启守护进程，命令末尾仅支持参数：-d';
    const CMD_SERVER_MISSING_4 = '单元测试错误：请输入需要测试的路由';
    const CMD_SERVER_MISSING_5 = '单元测试错误：该路由不存在';
    const CMD_SERVER_MISSING_6 = '单元测试错误：该路由暂无相应用例';
    const CMD_SERVER_MISSING_7 = '单元测试错误：暂不支持WebSocket服务的用例调试';
    const CMD_SERVER_MISSING_8 = '单元测试错误：参数错误';
    const CMD_SERVER_MISSING_9 = '指令错误：暂不支持该指令';
    const CMD_SERVER_MISSING_10 = '指令错误：暂未找到Swoole.pid缓存文件，路径为：';
    const CRONTAB_1 = '定时器挂载错误：定时器Class命名空间地址错误：';
    const ROUTE_1 = '路由地址不存在~';
    const RPC_SERVER_1 = '指令错误：sw-x rpc 缺少更多的命令参数';
    const RPC_SERVER_2 = '指令错误：RPC服务中心正确的安装指令是：sw-x rpc start';
    const RPC_SERVER_3 = '指令错误：sw-x rpc WEB组件，只允许设置为服务中心的应用，才能部署安装。请在/config/rpc.php 文件中进行设置';
    const HTTP_MONITOR_1 = '指令错误：sw-x monitor 缺少更多的命令参数';
    const HTTP_MONITOR_2 = '指令错误：HTTP请求监控组件正确的安装指令是：sw-x monitor start';
    const HTTP_CONTROLLER_1 = '指令错误：sw-x controller 缺少更多的命令参数';
    const HTTP_CONTROLLER_2 = '指令错误：sw-x controller 指令的第二个参数，只支持：http、websocket';
    const HTTP_CONTROLLER_3 = '指令错误：sw-x controller 缺少命令参数3';
    const HTTP_CONTROLLER_4 = '指令错误：sw-x controller 参数4 为控制器路由，不允许传入 / ';
    const HTTP_CONTROLLER_5 = '指令错误：sw-x controller 路由已存在';
    const HTTP_CONTROLLER_6 = '指令错误：sw-x controller 没有权限创建路由目录！';
    const HTTP_CONTROLLER_7 = '指令错误：sw-x controller 没有权限，控制器创建失败！';
    const HTTP_CONTROLLER_8 = '文件已存在！';
    const HTTP_CONTROLLER_9 = '控制器创建完成！';
}