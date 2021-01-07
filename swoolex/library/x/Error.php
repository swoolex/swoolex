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
     * 错误输出
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
