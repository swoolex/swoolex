<?php
/**
 * +----------------------------------------------------------------------
 * 控制台-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

class AbstractConsole {

    /**
     * 打印出框架头部说明
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected static function head_description() {
        echo PHP_EOL;
        echo "+----------------------------------------------------------+".PHP_EOL;
        echo "|   _____                                     __    __     |".PHP_EOL;
        echo "|  / ____|                                    \ \  / /     |".PHP_EOL;
        echo "| | (___     __      __                        \ \/ /      |".PHP_EOL;
        echo "|  \___ \    \ \ /\ / /        _______          \  /       |".PHP_EOL;
        echo "|  ____) |    \ V  V /        |_______|         /  \       |".PHP_EOL;
        echo "| |_____/      \_/\_/                          / /\ \      |".PHP_EOL;
        echo "|                                             /_/  \_\     |".PHP_EOL;   
        echo "+----------------------------------------------------------+".PHP_EOL; 
        echo PHP_EOL;
    }

    /**
     * 打印启动项的使用说明
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected function start_action() {
        self::head_description();
        echo 'USAGE: php sw-x commond'.PHP_EOL;
        echo '1. start [服务类型]，以DeBug模式开启服务，此时服务不会以Daemon形式运行'.PHP_EOL;
        echo '2. start [服务类型] -d，以Daemon模式开启服务'.PHP_EOL;
        echo '3. status，查看服务器的状态'.PHP_EOL;
        echo '4. stop，停止服务器'.PHP_EOL;
        echo '5. reload，热加载所有业务代码'.PHP_EOL;
        echo '6. test [服务类型] [路由地址]'.PHP_EOL;
        echo '7. controller [服务类型] [路由地址] [方法名称] [路由名称]'.PHP_EOL;
        echo '8. monitor start，创建HTTP请求监控WEB服务组件'.PHP_EOL.PHP_EOL;
        echo '9. rpc start，创建HTTP-RPC 控制台WEB服务组件'.PHP_EOL.PHP_EOL;
        echo 'SERVER: Types of services supported'.PHP_EOL;
        echo '1. http，WEB服务'.PHP_EOL;
        echo '2. websocket，WebSocket服务'.PHP_EOL;
        echo '3. rpc，Tcp-Rpc服务'.PHP_EOL;
        echo '4. mqtt，Tcp-MQTT服务'.PHP_EOL.PHP_EOL;
    }

    /**
     * 启动脚本错误，输出命令行内容
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @param string $error
     * @param string $status 是否输出头
     * @return void
    */
    public static function exit_error($error, $status=true) {
        self::head_description();
        if ($status) echo 'SwooleX-ERROR，';
        echo $error.PHP_EOL.PHP_EOL;
        exit;
    }

    /**
     * 打印出服务状态详细信息
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected function start_yes() {
        $scheduler = new \Swoole\Coroutine\Scheduler;
        $scheduler->add(function () {
            self::head_description();

            echo "\033[1A\n\033[K-----------------------\033[47;30m SwooleX Server \033[0m--------------------------\n\033[0m";
            echo "Swoole-Version：".swoole_version().PHP_EOL;
            echo "CPU_nums：".swoole_cpu_num().PHP_EOL;
            echo "SwooleX-Version：".VERSION." Beta".PHP_EOL;
            echo "PHP Version：".PHP_VERSION.PHP_EOL;
            echo "Server Type：".$this->_server_start['server'].PHP_EOL;
            echo "Host：".$this->config['host'].PHP_EOL;
            echo "Port：".$this->config['port'].PHP_EOL;
            if ($this->config['ssl_cert_file'] && $this->config['ssl_key_file']) {
                echo "SSL：Yes".PHP_EOL;
            } else {
                echo "SSL：No".PHP_EOL;
            }
            if ($this->_server_start['option']=='-d') {
                echo "Daemonize：Yes".PHP_EOL;
            } else {
                if ($this->config['daemonize'] == true) {
                    echo "Daemonize：Yes".PHP_EOL;
                } else {
                    echo "Daemonize：-- 未知，参考status指令 查看进程是否存活".PHP_EOL;
                }
            }
            echo "Memory_get_usage：".$this->memory().PHP_EOL;

            echo "Mysql_connect_count（5S）：".$this->create_mysql_pool_log().PHP_EOL;
            echo "Redis_connect_count（5S）：".$this->create_redis_pool_log().PHP_EOL;
            echo "MongoDb_connect_count（5S）：".$this->create_mongodb_pool_log().PHP_EOL;
            echo "RabbitMQ_connect_count（5S）：".$this->create_rabbitmq_pool_log().PHP_EOL;
            
            echo PHP_EOL;
        });
        $scheduler->start();
    }
    
    /**
     * 获取当前内存占用大小
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function memory(){ 
        // 查出进程ID
        $shell = 'ss -antulp | grep '.\x\Config::get('server.port');
        
        $arr = \Swoole\Coroutine\System::exec($shell);
        if (empty($arr['output'])) {
            return $shell.' Acquisition failed';
        }

        $str = $arr['output'];
        $arr = explode('pid=', $str);
        $arr = explode(',', $arr[1]);
        $pid = $arr[0];
        
        // 查出内存栈
        $shell = 'cat /proc/'.$pid.'/status';
        $arr = \Swoole\Coroutine\System::exec($shell);
        if (empty($arr['output'])) {
            return $shell.' Acquisition failed';
        }

        $str = $arr['output'];
        $arr = explode('VmRSS:', $str);
        $arr = explode('kB', $arr[1]);
        $size = trim($arr[0]) * 1000;
        
        $unit = '';
        if ($size >= 1073741824) {
            $size = ($size / 1073741824);
            $unit = 'G';
        } elseif ($size >= 1048576) {
            $size = ($size / 1048576);
            $unit = 'M';
        } elseif ($size >= 1024) {
            $size = ($size / 1024);
            $unit = 'K';
        } else {
            $size = $size;
        }
        return round($size, 2).' '.$unit.'B';
    }

    /**
     * 读取Mysql连接数日志
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function create_mysql_pool_log() {
        // MYSQL连接数
        $path = BOX_PATH.'env'.DS.'mysql_pool_num.count';
        $json = file_get_contents($path);
        $array = [];
        if ($json) {
            $array = json_decode($json, true);
        }
        $mysql_pool_num = 0;
        foreach ($array as $v) {
            $mysql_pool_num += $v;
        }
        return $mysql_pool_num;
    }
    
    /**
     * 读取Redis连接数日志
     * @todo 无
     * @author 小黄牛
     * @version v1.2.1 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function create_redis_pool_log() {
        //Redis连接数
        $path = BOX_PATH.'env'.DS.'redis_pool_num.count';
        $json = file_get_contents($path);
        $array = [];
        if ($json) {
            $array = json_decode($json, true);
        }
        $redis_pool_num = 0;
        foreach ($array as $v) {
            $redis_pool_num += $v;
        }
        return $redis_pool_num;
    }
    
    /**
     * 读取MongoDb连接数日志
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function create_mongodb_pool_log() {
        //Redis连接数
        $path = BOX_PATH.'env'.DS.'mongodb_pool_num.count';
        $json = file_get_contents($path);
        $array = [];
        if ($json) {
            $array = json_decode($json, true);
        }
        $pool_num = 0;
        foreach ($array as $v) {
            $pool_num += $v;
        }
        return $pool_num;
    }

    /**
     * 读取RabbitMQ连接数日志
     * @todo 无
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function create_rabbitmq_pool_log() {
        //Redis连接数
        $path = BOX_PATH.'env'.DS.'rabbitmq_pool_num.count';
        $json = file_get_contents($path);
        $array = [];
        if ($json) {
            $array = json_decode($json, true);
        }
        $pool_num = 0;
        foreach ($array as $v) {
            $pool_num += $v;
        }
        return $pool_num;
    }
}