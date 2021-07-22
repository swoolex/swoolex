<?php
/**
 * +----------------------------------------------------------------------
 * 错误异常监听
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Error {
    private static $instance = null;
    private function __construct(){}
    private function __clone(){}

     // 数据库检测异常的内容跳过
     protected $breakStr = [
        'PDO::getAttribute(): send of',
        'Error while sending STATISTICS packet. PID=',
    ];

    /**
     * 实例化对象方法，供外部获得唯一的对象
    */
    public static function run(){
        if (empty(self::$instance)) {
            self::$instance = new Error();
        }
        return self::$instance;
    }

    /**
     * 注册错误异常监听
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function register() {
        # 致命错误捕捉
        register_shutdown_function([\x\Error::run(), 'deadlyError']);
        # 异常捕捉
        set_error_handler([\x\Error::run(), 'appError']); 
    }

    /**
     * 普通错误异常捕捉
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @param int $errcontext 错误上下文
     * @return void
    */
    public function appError($errno, $errstr, $errfile, $errline, $errcontext) {
        $error = [];
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                //ob_end_clean();
                $error['message'] = $errstr;
                $error['file'] = $errfile;
                $error['line'] = $errline;
                break;
            default:
                $error['message'] = $errstr;
                $error['file'] = $errfile;
                $error['line'] = $errline;
                break;
        }
        if (strpos($error['message'], 'MySQL server has gone away')===false){
            $this->halt2($error);
        }
    }

    /**
     * 致命异常错误捕捉
     * @return void
    */
    public function deadlyError() {
        if ($e = error_get_last()) {
            $error = [];
            switch($e['type']){
              case E_ERROR:
              case E_PARSE:
              case E_CORE_ERROR:
              case E_COMPILE_ERROR:
              case E_USER_ERROR:
                $error['message'] = $e['message'];
                $error['file'] = $e['file'];
                $error['line'] = $e['line'];
                $error['deadlyError'] = true;
                $this->halt2($error);
                break;
            }
        }
    }

    /**
     * 获取出错文件内容
     * 获取错误的前9行和后9行
     * @param string $file 错文件地址
     * @param int $line 错误行数
     * @return array 错误文件内容
    */
    protected function getSourceCode($file, $line) {
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($file);
            $source   = [
                'first'  => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (\Exception $e) {
            $source = [];
        }
        return $source;
    }

    /**
     * PHP错误输出
     * @param mixed $error 错误
     * @return void
    */
    public function halt2($error) {
        $e = [];
        # 获得错误信息
        $e['file']    = $error['file'];
        $e['line']    = $error['line'];
        $data         = explode('in '.$error['file'], $error['message']);
        $e['message'] = $data[0];
        $e['trace']   = debug_backtrace();
        # 数据库重连异常的跳过记录
        foreach ($this->breakStr as $msg) {
            if (stripos($e['message'], $msg) !== false) {
                return true;
            }
        }

        # 获得错误上下文内容
        $source       = $this->getSourceCode($e['file'], $e['line']);

        $txt  = 'ThrowableError in：'.$e['file'].'，Line：'. $e['line'];
        $txt .= '行， 原因：'.nl2br(htmlentities($e['message']));

		# 开启调试模式则记录错误日志
        if (\x\Config::get('app.error_log_status') == true) {
            # 第一次异常才写入日志
            \x\entity\Log::run($txt); 
        }
        # 错误处理的生命周期回调 - 普通异常才回调，错误异常已经跳出协程底层了
        if (!isset($error['deadlyError'])) {
            \design\Lifecycle::controller_error($e, $txt, $source);
        }

        $this->context_delete();

        return true;
    }

    /**
     * Swoole底层错误输出
     * @param throwable $throwable 错误
     * @return void
    */
    public function halt($throwable) {
        $trace = $throwable->getTrace();
        $start = current($trace);
        $e = [];
        # 获得错误信息
        $e['file']    = $start['file'] ?? $start['class'];
        $e['line']    = $throwable->getLine();
        $e['message'] = $throwable->getMessage();
        $e['trace']   = $trace;
        # 数据库重连异常的跳过记录
        foreach ($this->breakStr as $msg) {
            if (stripos($e['message'], $msg) !== false) {
                return true;
            }
        }
        
        # 获得错误上下文内容
        $source       = isset($start['file']) ? $this->getSourceCode($e['file'], $e['line']) : ['first'=>'', 'source'=>[]];

        $txt  = 'ThrowableError in：'.$e['file'].'，Line：'. $e['line'];
        $txt .= '行， 原因：'.nl2br(htmlentities($e['message']));

		# 开启调试模式则记录错误日志
        if (\x\Config::get('app.error_log_status') == true) {
            # 第一次异常才写入日志
            \x\entity\Log::run($txt); 
        }
        # 错误处理的生命周期回调
        \design\Lifecycle::controller_error($e, $txt, $source);

        $this->context_delete();

        return true;
    }

    /**
     * 销毁上下文
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function context_delete() {
        // 销毁上下文
        \x\context\Request::delete();
        \x\context\Response::delete();
        \x\context\Container::delete();
    }
}