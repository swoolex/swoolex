<?php
/**
 * +----------------------------------------------------------------------
 * 热重置组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\common;

class Reload
{
    /**
     * 需要监听的目录
    */
    private static $_list = [];
    /**
     * 需要监听的文件后缀
    */
    private static $monitor_suffix;
    /**
     * 需要监听的目录
    */
    private static $monitor_list = [];
    /**
     * 需要移除监听的目录
    */
    private static $remove_list = [];
    /**
     * 扫描目录间隔时间
    */
    private static $interval_time;
    // 初始化目录
    public static function init() {
        $config = \x\Config::get('reload');
        self::$interval_time = $config['interval_time'];
        self::$monitor_suffix = explode(',', str_replace([' ', '.'], '', $config['monitor_suffix']));
        self::init_insert($config['monitor_list']);
        self::init_remove($config['remove_list']);
        return self::$_list;
    }
    // 初始化监听
    private static function init_insert($monitor_list) {
        foreach ($monitor_list as $dir) {
            self::insert($dir, true);
        }
    }
    // 全部移除
    private static function init_remove($remove_list) {
        foreach ($remove_list as $dir) {
            $dir = str_replace(ROOT_PATH, '', $dir);
            self::delete($dir);
        }
    }
    // 监听一个目录
    public static function insert($dir, $status=false) {
        $dir = ROOT_PATH.rtrim(ltrim($dir, '/'), '/');

        if (is_dir($dir)) {
            self::$monitor_list[$dir] = 1;
            self::get_directory($dir, 1);
        } else {
            if (file_exists($dir)) {
                self::$_list[$dir] = filemtime($dir);
            }
        }
        
        if ($status == false) {
            self::init_remove(self::$remove_list);
        }
    }
    // 移除一个目录
    public static function delete($dir) {
        $dir = ROOT_PATH.rtrim(ltrim($dir, '/'), '/');
        if (is_dir($dir)) {
            self::get_directory($dir, 2);
            self::$remove_list[$dir] = $dir;
        } else if (isset(self::$_list[$dir])) {
            unset(self::$_list[$dir]);
        }
    }
    // 挂载扫描
    public static function timer($server) {
        $times = self::$interval_time * 1000;
        \Swoole\Timer::tick($times, function () use($server) {
            $status = false;
            $length = count(self::$_list);
            foreach (self::$monitor_list as $dir=>$k) {
                $status = self::timer_directory($dir);
                if ($status) break;
            }
            self::init_remove(self::$remove_list);
            if ($status == false) {
                if ($length != count(self::$_list)) $status = true;
            }
            if ($status) {
                $server->reload();
            }
        });
        return self::$_list;
    }
    // 定时器递归文件更新
    private static function timer_directory($dir, $status=false) {
        if ($headle=opendir($dir)) {
            while ($file=readdir($headle)) {
                $file = iconv("gb2312", "utf-8", $file);
                if ($file!='.' && $file!='..' ) {
                    $file = $dir . DS . $file;
                    foreach (self::$remove_list as $delete_dir) {
                        if (stripos($file, $delete_dir) !== false) continue;
                    }
                    if (is_file($file)) {
                        $suffix = pathinfo($file, PATHINFO_EXTENSION);
                        if (in_array($suffix, self::$monitor_suffix) == false) continue;
                        
                        $time = filemtime($file);
                        // 新文件
                        if (!isset(self::$_list[$file])) {
                            $status = true;
                            self::$_list[$file] = $time;
                        // 文件日期更新了
                        } else if (self::$_list[$file] != $time){
                            $status = true;
                            self::$_list[$file] = $time;
                        }
                    }else{
                        return self::timer_directory($file, $status);
                    }
                }
            }
            closedir($headle);
        }
        return $status;
    }
    // 主动递归出全部文件
    private static function get_directory($dir, $type) {
        if ($headle=opendir($dir)) {
            while ($file=readdir($headle)) {
                $file = iconv("gb2312", "utf-8", $file);
                if ($file!='.' && $file!='..' ) {
                    $file = $dir . DS . $file;
                    if (is_file($file)) {
                        if ($type == 1) {
                            $suffix = pathinfo($file, PATHINFO_EXTENSION);
                            if (in_array($suffix, self::$monitor_suffix) == false) continue;
                            
                            self::$_list[$file] = filemtime($file);
                        } else if (isset(self::$_list[$file])) {
                            unset(self::$_list[$file]);
                        }
                    }else{
                        self::get_directory($file, $type);
                    }
                }
            }
            closedir($headle);
        }
    }
}