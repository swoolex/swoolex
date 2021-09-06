<?php
/**
 * +----------------------------------------------------------------------
 * 日志挂载类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;

class Log
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 允许操作的目录 
    */
    private $_path = [
        'view' => 'view',
        'log' => 'log',
        'sql' => 'sql',
        'mqtt' => 'mqtt',
    ];

    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt
     * @return App
    */
    public static function run($txt=null){
        if (empty(self::$instance)) {
            self::$instance = new static();
        }
        if ($txt) {
            self::$instance->setLog($txt);
        }

        return self::$instance;
    }

    /**
     * 挂载日志
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt
     * @return void
    */
    public function setLog($txt) {
        $path = $this->_path['log'].'/'.date('Y-n-j', time()).'.log';

        $myfile = fopen($path, "a+");
        fwrite($myfile, $this->format($txt));
        fclose($myfile);
    }

    /**
     * 挂载MQTT请求记录
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt
     * @return void
    */
    public function setMqtt($txt) {
        $path = $this->_path['mqtt'].'/'.date('Y-n-j', time()).'.log';

        \Swoole\Coroutine\System::writeFile($path, $txt, FILE_APPEND);
    }

    /**
     * 挂载SQL
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt
     * @return void
    */
    public function sql($txt) {
        $path = $this->_path['sql'].'/'.date('Y-n-j', time()).'.log';

        $myfile = fopen($path, "a+");
        fwrite($myfile, $this->format($txt));
        fclose($myfile);
    }

    /**
     * 检测目录是否创建
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function start() {
        if (!file_exists(WORKLOG_PATH.'/')) {
            mkdir(WORKLOG_PATH.'/', 0755);
        }

        foreach ($this->_path as $k=>$v) {
            $this->_path[$k] = WORKLOG_PATH.'/'.$v;

            // 目录不存在则挂载
            if (!file_exists($this->_path[$k])) {
                mkdir($this->_path[$k].'/', 0755);
            }
        }

        \design\StartRecord::log();
    }

    /**
     * 格式化日志内容
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt
     * @return void
    */
    private function format($txt) {
        return '【'.date('Y-m-d H:i:s', time()).'】 '.$txt."\r\n";
    }
}