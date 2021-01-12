<?php
// +----------------------------------------------------------------------
// | 错误异常监听
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Error {
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象

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
        $this->halt2($error);
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
                //ob_end_clean();
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
        # 获得错误上下文内容
        $source       = $this->getSourceCode($e['file'], $e['line']);

        $txt  = 'ThrowableError in：'.$e['file'].'，Line：'. $e['line'];
        $txt .= '行， 原因：'.nl2br(htmlentities($e['message']));

		# 开启调试模式则记录错误日志
        if (\x\Config::run()->get('app.de_bug') == true) {
            # 第一次异常才写入日志
            \x\Log::run($txt); 
        }
        # 错误处理的生命周期回调 - 普通异常才回调，错误异常已经跳出协程底层了
        if (!isset($error['deadlyError'])) {
            $obj = new \lifecycle\controller_error();
            $obj->run($e, $txt, $source);
            unset($obj);
        }

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
        $e['file']    = $start['file'];
        $e['line']    = $throwable->getLine();
        $e['message'] = $throwable->getMessage();
        $e['trace']   = $trace;
        # 获得错误上下文内容
        $source       = $this->getSourceCode($e['file'], $e['line']);

        $txt  = 'ThrowableError in：'.$e['file'].'，Line：'. $e['line'];
        $txt .= '行， 原因：'.nl2br(htmlentities($e['message']));

		# 开启调试模式则记录错误日志
        if (\x\Config::run()->get('app.de_bug') == true) {
            # 第一次异常才写入日志
            \x\Log::run($txt); 
        }
        # 错误处理的生命周期回调
        $obj = new \lifecycle\controller_error();
        $obj->run($e, $txt, $source);
        unset($obj);

        return true;
    }
}
