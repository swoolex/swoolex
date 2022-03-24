<?php
/**
 * +----------------------------------------------------------------------
 *  HTTP服务onRequest事件的监控组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

class MonitorHttpEvent {

    /**
     * 开始
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function start($server, $config, $request, $response) {
        try {
            # 防止Chrome的空包
            $uri = ltrim($request->server['request_uri'], '/');
            if ($uri == 'favicon.ico') {
                $response->status(404);
                $response->end();
                return false;
            }

            // 记录上下文
            \x\context\Request::set($request);
            \x\context\Response::set($response);
            \x\context\Container::set('websocket_server', $server);

            $ip = $server->getClientInfo($request->fd)['remote_ip'];
            // 触发限流器
            if (\x\Limit::ipVif($server, $request->fd, $ip, 'http') == false) {
                // 销毁上下文
                \x\context\Request::delete();
                \x\context\Response::delete();
                \x\context\Container::delete();
                return false;
            }
            
            // 根目录
            $dir_root = \x\Config::get('server.http_monitor_dir_root');
            // 存储目录
            $dir_date = $dir_root.date('Y_m_d').DS;
            // 记录日志详情
            $dir_log = $dir_date.'log'.DS;
            // 已结束的日志名称
            $dir_close = $dir_date.'close.json';
            // 进行中的日志名称
            $dir_open = $dir_date.'open.json';
            // md5(路由名称)创建目录，并存储对应日志名称
            $dir_route = $dir_date.'route'.DS;
            // 创建目录
            self::monitor_file_create($dir_root, $dir_date, $dir_log, $dir_close, $dir_open, $dir_route);
            // 请求唯一标识
            $request_process = time().'_'.$request->fd.'.log';
            // 创建监控日志
            self::monitor_start($dir_log, $dir_close, $dir_open, $dir_route, $request_process, $request);

            // 跨域配置设置
            if ($config['origin']) $response->header('Access-Control-Allow-Origin', $config['origin']); 
            if ($config['type']) $response->header('Content-Type', $config['type']); 
            if ($config['methods']) $response->header('Access-Control-Allow-Methods', $config['methods']); 
            if ($config['credentials']) $response->header('Access-Control-Allow-Credentials', $config['credentials']); 
            if ($config['headers']) $response->header('Access-Control-Allow-Headers', $config['headers']); 

            // 注入调试内容
            if (\x\Config::get('app.de_bug')) {
                // 请求开始时间
                \x\context\Container::set('http_start_time', microtime(true));
                // 请求开始内容消耗
                \x\context\Container::set('http_start_cpu', memory_get_usage());
            }

            # 开始转发路由
            $obj = new \x\route\Http($server, $request->fd);
            $obj->start();

            // 调用二次转发，不做重载
            $on = new \box\event\server\onRequest($server, $config);
            $on->run();
            
            // 销毁上下文
            \x\context\Request::delete();
            \x\context\Response::delete();
            \x\context\Container::delete();

            // 结束监控日志
            self::monitor_end($dir_log, $dir_close, $dir_open, $dir_route, $request_process);
        } catch (\Throwable $throwable) {
            // 结束监控日志
            self::monitor_end($dir_log, $dir_close, $dir_open, $dir_route, $request_process, $throwable);
            return \x\Error::run()->halt($throwable);
        }
    }

    /**
     * 创建监控文件目录
     * @todo 无
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function monitor_file_create($dir_root, $dir_date, $dir_log, $dir_close, $dir_open, $dir_route) {
        if (\x\Config::get('server.http_monitor_status') == false) {
            return false;
        } 

        if (is_dir($dir_root) == false) {
            mkdir($dir_root, 0755);
        }

        if (is_dir($dir_date) == false) {
            mkdir($dir_date, 0755);
            // 创建菜单目录
            mkdir($dir_log, 0755);
            \Swoole\Coroutine\System::writeFile(rtrim($dir_log, '/').'.json', '');
            \Swoole\Coroutine\System::writeFile($dir_close, '');
            \Swoole\Coroutine\System::writeFile($dir_open, '');
            mkdir($dir_route, 0755);
        }
    }

    /**
     * 创建请求开始日志
     * @todo 无
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function monitor_start($dir_log, $dir_close, $dir_open, $dir_route, $request_process, $request) {
        if (\x\Config::get('server.http_monitor_status') == false) {
            return false;
        } 
        // 写入日志记录
        \Swoole\Coroutine\System::writeFile(rtrim($dir_log, '/').'.json', $request_process.'|', FILE_APPEND);

        // 记录日志内容
        \Swoole\Coroutine\System::writeFile($dir_log.$request_process, json_encode([
            'fd' => $request->fd, // Swoole请求标识
            'start_time' => date('Y-m-d H:i:s', time()), // 请求开始时间
            'status' => 1, // 请求状态
            'request_method' => $request->server['request_method'], // 请求类型
            'server_protocol' => $request->server['server_protocol'], // 请求协议
            'route' => $request->server['request_uri'], // 路由
            'query_string' => $request->server['query_string'] ?? [], // URL参数
            'header' => $request->header, // 请求头
            'get' => $request->get ?? [], // GET参数
            'post' => $request->post ?? [], // POST参数
            'is_error' => 0, // 是否有报错
            'error_file' => '', // 错误文件地址
            'error_line' => '', // 错误文件行数
            'error_message' => '', // 错误内容
        ], JSON_UNESCAPED_UNICODE));

        // 记录进行中的请求
        \Swoole\Coroutine\System::writeFile($dir_open, $request_process.'|', FILE_APPEND);

        // 写入路由地址隐射文件
        $dir_route = $dir_route.md5($request->server['request_uri']).'.json';
        \Swoole\Coroutine\System::writeFile($dir_route, $request_process.'|', FILE_APPEND);
    }

    /**
     * 结束请求日志
     * @todo 无
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function monitor_end($dir_log, $dir_close, $dir_open, $dir_route, $request_process, $throwable=null) {
        if (\x\Config::get('server.http_monitor_status') == false) {
            return false;
        } 
        // 销毁标记开始文件
        $content = str_replace($request_process.'|', '', \Swoole\Coroutine\System::readFile($dir_open));
        \Swoole\Coroutine\System::writeFile($dir_open, $content);
        // 记录结束的请求
        \Swoole\Coroutine\System::writeFile($dir_close, $request_process.'|', FILE_APPEND);
        // 读取日志文件
        $json = \Swoole\Coroutine\System::readFile($dir_log.$request_process);
        $array = [];
        if ($json) {
            $array = json_decode($json, true);
        }
        // 请求结束时间
        $array['end_time'] = date('Y-m-d H:i:s', time());
        $array['status'] = 2;

        // 如果有报错
        if ($throwable) {
            $array['is_error'] = 1;
            // 写入报错日志 
            $trace = $throwable->getTrace();
            $start = current($trace);
            $array['error_file']    = $start['file'] ?? '';
            $array['error_line']    = $throwable->getLine();
            $array['error_message'] = $throwable->getMessage();
        }
        // 更新日志内容
        \Swoole\Coroutine\System::writeFile($dir_log.$request_process, json_encode($array, JSON_UNESCAPED_UNICODE));
    }
}