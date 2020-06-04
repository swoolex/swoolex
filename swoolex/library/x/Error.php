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
    /**
     * 注册错误异常监听
     * @return void
    */
    public static function register() {
        # 致命错误捕捉
        register_shutdown_function('\x\Error::deadlyError');
        # 异常捕捉
    	set_error_handler('\x\Error::appError'); 
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
    public static function appError($errno, $errstr, $errfile, $errline, $errcontext) {
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
        self::halt($error);
    }

    /**
     * 致命异常错误捕捉
     * @return void
    */
    public static function deadlyError() {
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
                self::halt($error);
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
    protected static function getSourceCode($file, $line) {
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($file);
            $source   = [
                'first'  => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (Exception $e) {
            $source = [];
        }
        return $source;
    }

    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
    */
    public static function halt($error) {
        $e = [];
        # 获得错误信息
        $e['file']    = $error['file'];
        $e['line']    = $error['line'];
        $data         = explode('in '.$error['file'], $error['message']);
        $e['message'] = $data[0];
        #$e['trace']     = debug_backtrace();
        # 获得错误上下文内容
        $source         = self::getSourceCode($e['file'], $e['line']);

        $txt  = 'ThrowableError in：'.$e['file'].'，Line：'. $e['line'];
        $txt .= '行， 原因：'.nl2br(htmlentities($e['message']));

		# 开启调试模式则记录错误日志
        if (\x\Config::run()->get('app.de_bug') == true) {
            # 第一次异常才写入日志
            if (stripos($source['source'][1], '# 开启调试模式则记录错误日志') === false) {
                \x\Log::run($txt); 
            }
        }

        throw new \Exception($txt."\n");
    }
}
