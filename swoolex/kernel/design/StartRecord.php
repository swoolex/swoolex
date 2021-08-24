<?php
/**
 * +----------------------------------------------------------------------
 * 框架启动时的日志记录
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

class StartRecord {
    /**
     * 开始时间
    */
    private static $start_time;
    /**
     * 配置文件初始化
    */
    public static function config($time) {
        self::$start_time = $time;
        self::clean_log();
        self::insert_log('配置文件初始化完成');
    }
    /**
     * 错误异常注册
    */
    public static function error() {
        self::insert_log('错误异常注册完成');
    }
    /**
     * 日志模块初始化
    */
    public static function log() {
        self::insert_log('日志模块初始化完成');
    }
    /**
     * 开箱工作
    */
    public static function unpacking() {
        self::insert_log('服务开箱工作初始化完成');
    }
    /**
     * 清除ENV文件
    */
    public static function clean_env() {
        self::insert_log('框架ENV环境变量缓存文件初始化完成');
    }
    /**
     * MQTT内存表创建
    */
    public static function mqtt_table() {
        self::insert_log('MQTT服务专属Swoole-Table内存表初始化完成');
    }
    /**
     * 服务事件绑定
    */
    public static function server_event() {
        self::insert_log('Swoole-Server消息事件绑定结束');
    }
    /**
     * HTTP注解解析完成
    */
    public static function http_doc_reload() {
        self::insert_log('HTTP服务-注解解析完成');
    }
    /**
     * WebSocket服务解析完成
    */
    public static function websocket_doc_reload() {
        self::insert_log('WebSocket服务-注解解析完成');
    }
    /**
     * Rpc服务解析完成
    */
    public static function rpc_doc_reload() {
        self::insert_log('Rpc服务-注解解析完成');
    }
    /**
     * Mqtt服务解析完成
    */
    public static function mqtt_doc_reload() {
        self::insert_log('Mqtt服务-注解解析完成');
    }
    /**
     * Mysql连接池
    */
    public static function mysql_reload($time) {
        self::$start_time = $time;
        self::insert_log('Mysql连接池解析完成');
    }
    /**
     * Mysql连接池-监控
    */
    public static function mysql_monitor() {
        self::insert_log('Mysql连接数统计监控器启动成功');
    }
    /**
     * Redis连接池
    */
    public static function redis_reload($time) {
        self::$start_time = $time;
        self::insert_log('Redis连接池解析完成');
    }
    /**
     * Mysql连接池-监控
    */
    public static function redis_monitor() {
        self::insert_log('Redis连接数统计监控器启动成功');
    }
    /**
     * Rpc服务中心监控器
    */
    public static function rpc_service_monitor() {
        self::insert_log('Rpc服务中心监控器启动成功');
    }
    /**
     * MQTT服务设备在线状态监控器
    */
    public static function mqtt_service_monitor() {
        self::insert_log('MQTT服务设备在线状态监控器启动成功');
    }
    /**
     * 定时任务挂载
    */
    public static function crontab() {
        self::insert_log('定时任务挂载完成');
    }
    /**
     * 计算耗时
    */
    private static function count_time() {
        $end_time = explode(' ',microtime());
        $start = self::$start_time[0]+self::$start_time[1];
        $end = $end_time[0]+$end_time[1];
        $thistime = $end-$start;
        // 更新下一次起始耗时
        self::$start_time = explode(' ',microtime());

        return '，耗时：'.sprintf("%1\$.6f", $thistime).'s';
    }
    /**
     * 清空日志
    */
    private static function clean_log() {
        $path = WORKLOG_PATH.'start.log';
        return file_put_contents($path, '');
    }
    /**
     * 写入启动日志
    */
    private static function insert_log($txt) {
        $path = WORKLOG_PATH.'start.log';
        return file_put_contents($path, date('Y-m-d H:i:s').' '.$txt.self::count_time().PHP_EOL, FILE_APPEND);
    }
}