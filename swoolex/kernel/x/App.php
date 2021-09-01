<?php
/**
 * +----------------------------------------------------------------------
 * 服务启动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

use design\AbstractConsole;
use design\SystemTips as Tips;

class App extends AbstractConsole {
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}

    /**
     * 配置项 
    */
    protected $config;
    /**
     * 指令参数集
    */
    private $_argv;
    /**
     * 支持的服务类型
    */
    protected $_server_command = [
        'http',
        'websocket',
        'rpc',
        'mqtt',
    ];
    /**
     * 启动的服务参数
    */
    protected $_server_start = [
        'action' => null, // 指令集
        'server' => null, // 启动服务类型
        'option' => null, // 其余参数
    ];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return App
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new App();
            return self::$instance;
        }
    }

    /**
     * 启动服务
     * @todo 无
     * @author 小黄牛
     * @version v1.1.1 + 2020.07.08
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function start() {
        // 初始化配置项
        $this->setConfig();
        // 初始化指令参数
        $this->setCommand();
        // 命令分解
        $this->switchCommand();
    }


    //----------------------------------- 命令转发 -----------------------------------
    /**
     * 命令转发
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function switchCommand() {
        switch ($this->_server_start['action']) {
            // 启动服务
            case 'start':$this->cmdStart();break;
            // 热重启
            case 'reload':$this->cmdReload();break;
            // 查看服务状态
            case 'status':$this->cmdStatus();break;
            // 停止服务
            case 'stop':$this->cmdStop();break;
            // 单元测试服务
            case 'test':$this->cmdTest();break;
            // 没有的指令
            default:$this->cmdExtend();break;
        }
    }

    /**
     * 指令 - 启动服务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdStart() {
        if (empty($this->_server_start['server'])) {
            return self::exit_error(Tips::CMD_SERVER_MISSING_1);
        }

        if (in_array($this->_server_start['server'], $this->_server_command) == false) {
            return self::exit_error(Tips::CMD_SERVER_MISSING_2 . implode('、', $this->_server_command));
        }

        if ($this->_server_start['option'] && $this->_server_start['option'] != '-d') {
            return self::exit_error(Tips::CMD_SERVER_MISSING_3);
        }

        // 设置默认时区
        date_default_timezone_set(\x\Config::get('app.default_timezone'));
        // 开箱工作
        \x\common\Unpacking::run($this->_server_start['server']);
        // 清空ENV文件
        $this->reloadEnv();
        \design\StartRecord::clean_env();
        // 打印服务详情
        $this->start_yes();
        // 启动服务
        $service = new \x\Server(); 
        $service->start($this->_server_start['server'], $this->_server_start['option']);
    }
    /**
     * 指令 - 热重启
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdReload() {
        // 读取主进程ID 
        $idJson = file_get_contents($this->config['pid_file']);  
        $idArray = json_decode($idJson, true);
        // 清空ENV文件
        $this->reloadEnv(false);
        // 通知Swoole平滑重启进程
        posix_kill($idArray['manager_pid'], SIGUSR1);
    }
    /**
     * 指令 - 查看服务状态
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdStatus() {
        // 打印服务器字幕
        $this->start_yes();

        if (is_file($this->config['worker_pid_file'] ) && is_file($this->config['tasker_pid_file'])) {
            // 读取所有进程，并列出来
            $idsJson = file_get_contents($this->config['pid_file']);
            $idsArr = json_decode($idsJson, true);
            $workerPidString = rtrim(file_get_contents($this->config['worker_pid_file']), '|');
            $taskerPidString = rtrim(file_get_contents($this->config['tasker_pid_file']), '|');
            $workerPidArr = explode('|', $workerPidString);
            $taskerPidArr = explode('|', $taskerPidString);

            echo "Worker-Pid：".PHP_EOL;
            echo str_pad('Master', 22, ' ', STR_PAD_BOTH ),
                str_pad('Manager', 14, ' ', STR_PAD_BOTH ),
                str_pad('Worker_id', 5, ' ', STR_PAD_BOTH ),
                str_pad('Pid', 12, ' ', STR_PAD_BOTH).PHP_EOL;

            foreach ($workerPidArr as $workerPidItem) {
                $tempIdPid = explode(':', $workerPidItem);
                echo str_pad($idsArr['master_pid'], 22, ' ', STR_PAD_BOTH ),
                    str_pad($idsArr['manager_pid'], 14, ' ', STR_PAD_BOTH ),
                    str_pad($tempIdPid[0], 5, ' ', STR_PAD_BOTH);
                if (isset($tempIdPid[1])) echo str_pad($tempIdPid[1], 12, ' ', STR_PAD_BOTH);
                echo PHP_EOL.PHP_EOL;
            }
            echo "Tasker-Pid：".PHP_EOL;
            echo str_pad('Master', 22, ' ', STR_PAD_BOTH ),
                str_pad('Manager', 14, ' ', STR_PAD_BOTH ),
                str_pad('Tasker_id', 5, ' ', STR_PAD_BOTH ),
                str_pad('Pid', 12, ' ', STR_PAD_BOTH).PHP_EOL;
            foreach ($taskerPidArr as $taskerPidItem) {
                $tempIdPid = explode(':', $taskerPidItem);
                echo str_pad($idsArr['master_pid'], 22, ' ', STR_PAD_BOTH ),
                    str_pad($idsArr['manager_pid'], 14, ' ', STR_PAD_BOTH ),
                    str_pad($tempIdPid[0], 5, ' ', STR_PAD_BOTH);
                if (isset($tempIdPid[1])) echo str_pad($tempIdPid[1], 12, ' ', STR_PAD_BOTH);
                echo PHP_EOL;
            }
        }
    }
    /**
     * 指令 - 停止服务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdStop() {
        if (!file_exists($this->config['pid_file'])) {
            return self::exit_error(Tips::CMD_SERVER_MISSING_10.$this->config['pid_file']);
        }
        // 读取主进程ID
        $idJson = file_get_contents($this->config['pid_file']);  
        $idArray = json_decode($idJson, true);
        // 通知Swoole停止服务
        var_dump(posix_kill($idArray['master_pid'], SIGKILL));
    }
    /**
     * 指令 - 单元测试服务
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdTest() {
        $route_url = strtolower($this->_server_start['option']);
        if (!$route_url) return self::exit_error(Tips::CMD_SERVER_MISSING_4);
        
        
        switch ($this->_server_start['server']) {
            case 'http':
                $array = json_decode(file_get_contents($this->config['route_file']), true);
                $route = $array['http'];
                if ($route_url != '/') {
                    $route_url = ltrim($route_url, '/');
                }
                if (empty($route[$route_url])) {
                    return self::exit_error(Tips::CMD_SERVER_MISSING_5);
                }
                if (empty($route[$route_url]['own']['TestCase'])) {
                    return self::exit_error(Tips::CMD_SERVER_MISSING_6);
                }
                $this->http_test_case($route_url, $route[$route_url]);
            break;
            case 'websocket':
                return self::exit_error(Tips::CMD_SERVER_MISSING_7);
            break;
            default:
                return self::exit_error(Tips::CMD_SERVER_MISSING_8);
            break;
        }
    }
    /**
     * 指令 - 扩展导入
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function cmdExtend() {
        // 转发到外部扩展
        $cmd = ucfirst($this->_server_start['action']);
        $file = dirname(__FILE__).DS.'cmd'.DS.$cmd.'.php';
        if (!file_exists($file)) {
            return self::exit_error(Tips::CMD_SERVER_MISSING_9);
        }
        
        $class = "\x\cmd\\".$cmd;
        $obj = new $class();
        $res = $obj->run($this->_argv);
        unset($obj);
        return $res;
    }
    
    //----------------------------------- 工具箱 -----------------------------------
    /**
     * 初始化配置项
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function setConfig() {
        $this->config = \x\Config::get('server');

        $memory_limit = \x\Config::get('app.memory_limit');
        if ($memory_limit) {
            ini_set('memory_limit', $memory_limit);
        }
    }

    /**
     * 指令切割
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function setCommand() {
        global $argc, $argv;

        // CMD最多只兼容5个参数
        if ($argc <= 1 || $argc > 6 ) {
            $this->start_action();
            exit;
        }
        
        $this->_argv = $argv;
        $this->_server_start['action'] = $argv[1] ?? null;
        $this->_server_start['server'] = !empty($argv[2]) ? strtolower($argv[2]) : null;
        $this->_server_start['option'] = !empty($argv[3]) ? strtolower($argv[3]) : null;

        // 删除全局变量
        unset($argc);
        unset($argv);
    }

    /**
     * 单元测试调试-单条-HTTP
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由信息
     * @return void
    */
    private function http_test_case($url, $route) {
        $type = strtolower($route['method']);
        $url = '127.0.0.1:'.\x\Config::get('server.port').'/'.ltrim($url, '/');
        if ($type == 'get') {
            $url .= '?SwooleXTestCase=1';
        }

        // 这里什么都不用做，直接触发一次路由就行，请求里代个触发参数
        $curl = curl_init();  
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($type == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, [
                'SwooleXTestCase' => 1,
            ]);
        }

        // 单位 秒
        curl_setopt($curl, CURLOPT_TIMEOUT, 180);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);        
        $body = curl_exec($curl);
        curl_close($curl);
        
        echo $body;
    }

    /**
     * 初始化env环境文件
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否删除服务进程ID缓存
     * @return void
    */
    private function reloadEnv($status=true) {
        // 设置master进程别名
        swoole_set_process_name($this->config['master']);
        // 创建缓存目录
        if (!file_exists(WORKLOG_PATH)) mkdir(WORKLOG_PATH, 0755);
        $log = WORKLOG_PATH.'log'.DS;
        if (!file_exists($log)) mkdir($log, 0755);
        $sql = WORKLOG_PATH.'sql'.DS;
        if (!file_exists($sql)) mkdir($sql, 0755);
        $view = WORKLOG_PATH.'view'.DS;
        if (!file_exists($view)) mkdir($view, 0755);
        $crontab = WORKLOG_PATH.'crontab'.DS;
        if (!file_exists($crontab)) mkdir($crontab, 0755);

        // 服务进程ID
        if ($status) file_put_contents($this->config['pid_file'], '');
        // 工作进程ID
        file_put_contents($this->config['worker_pid_file'], '');
        file_put_contents($this->config['tasker_pid_file'], '');
        // MYSQL连接数
        $path = BOX_PATH.'env'.DS.'mysql_pool_num.count';
        file_put_contents($path, '{}');
        // Redis连接数
        $path = BOX_PATH.'env'.DS.'redis_pool_num.count';
        file_put_contents($path, '{}');
        // MongoDb连接数
        $path = BOX_PATH.'env'.DS.'mongodb_pool_num.count';
        file_put_contents($path, '{}');
        // 路由日志
        file_put_contents($this->config['route_file'], '');
    }
}