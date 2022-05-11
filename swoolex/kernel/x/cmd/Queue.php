<?php
/**
 * +----------------------------------------------------------------------
 * 自定义CMD命令 - HTTP-Queue 控制台组件创建
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\cmd;
use design\AbstractConsole;
use design\SystemTips;

class Queue {
    /**
     * 命令行参数
    */
    private $argv=[];

    /**
     * 调用入口
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @param array $argv
     * @return string
    */
    public function run($argv) {
        $this->argv = $argv;
        if (empty($argv[2])) return AbstractConsole::exit_error(SystemTips::QUEUE_SERVER_1 . PHP_EOL);
        if ($argv[2] != 'start') return AbstractConsole::exit_error(SystemTips::QUEUE_SERVER_2 . PHP_EOL);

        // 需要先创建开箱目录
        \x\common\Unpacking::create_app();
        \x\common\Unpacking::unpack_http(false);

        $this->copy_controller();
        $this->copy_view();

        $html  = '消息队列控制台Web组件安装完成！'.PHP_EOL.PHP_EOL;
        $html .= 'HTTP-Queue控制台-WEB路由地址：/HttpQueue/login'.PHP_EOL;
        $html .= '初始化账号密码在：/config/queue.php 文件中进行修改。'.PHP_EOL;

        return AbstractConsole::exit_error($html, false);
    }

    /**
     * 复制控制器到指定位置
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @return bool
    */
    private function copy_controller() {
        $dir = APP_PATH.'http/HttpQueue.php';
        if (file_exists($dir)) return false;

        return copy(BUILT_PATH.'/queue/http/HttpQueue.php', $dir);
    }

    /**
     * 复制视图到指定位置
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @return bool
    */
    private function copy_view() {
        $dir = APP_PATH.'view'.DS.'HttpQueue'.DS;
        if (file_exists($dir)) return false;
        mkdir($dir, 0755);

        $file = BUILT_PATH.'queue'.DS.'view'.DS;
        $temp = scandir($file);
        //遍历文件夹
        foreach ($temp as $v){
            if ($v=='.' || $v=='..') continue;
            copy($file.$v, $dir.$v);
        }

        return true;
    }

}