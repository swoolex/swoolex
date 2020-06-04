<?php
// +----------------------------------------------------------------------
// | 启动记录输出
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class StartEo
{
    private static $instance = null; // 创建静态对象变量,用于存储唯一的对象实例  
    private function __construct(){} // 私有化构造函数，防止外部调用
    private function __clone(){}     // 私有化克隆函数，防止外部克隆对象
    /**
     * 是否允许输出 
    */
    private static $status;
    
    /**
     * 实例化对象方法，供外部获得唯一的对象
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt 输出内容
     * @param string $type 颜色方案
     * @return void
    */
    public static function run($txt, $type='success'){
        if (empty(self::$instance)) {
            $obj = new StartEo();
            $obj::$status = \x\Config::run()->get('app.start_echo');
            self::$instance = $obj;
            self::$instance->_log(\x\Lang::run()->get('start -0'), $type);
        }

        self::$instance->_log($txt, $type);
    }

    /**
     * 输出
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $txt 输出内容
     * @param int $type 颜色方案
     * @return void
    */
    public function _log($txt, $type) {
        if (self::$status) {
            $color = '36m';
            if ($type == 'error') $color = '33m';
            
            echo "\033[".$color." ".date('Y-m-d H:i:s')."：\033[0m".$txt."...\n";

            // 输出完再抛出异常
            if ($type == 'error') throw new \Exception($txt);
        }
    }
}