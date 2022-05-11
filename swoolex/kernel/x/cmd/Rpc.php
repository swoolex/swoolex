<?php
/**
 * +----------------------------------------------------------------------
 * 自定义CMD命令 - HTTP-RPC 控制台组件创建
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

class Rpc {
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
        if (empty($argv[2])) return AbstractConsole::exit_error(SystemTips::RPC_SERVER_1 . PHP_EOL);
        if ($argv[2] != 'start') return AbstractConsole::exit_error(SystemTips::RPC_SERVER_2 . PHP_EOL);

        if (\x\Config::get('rpc.http_rpc_is') != true) {
            return AbstractConsole::exit_error(SystemTips::RPC_SERVER_3 . PHP_EOL);
        }

        // 需要先创建开箱目录
        \x\common\Unpacking::create_app();
        \x\common\Unpacking::unpack_http(false);

        $this->copy_controller();
        $this->copy_view();

        $html  = 'RPC服务中心Web组件安装完成！'.PHP_EOL.PHP_EOL;
        $html .= 'HTTP-RPC服务中心-WEB路由地址：/HttpRpc/login'.PHP_EOL;
        $html .= '初始化账号密码在：/config/rpc.php 文件中进行修改。'.PHP_EOL;

        return AbstractConsole::exit_error($html, false);
    }

    /**
     * 复制控制器到指定位置
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @return bool
    */
    private function copy_controller() {
        $dir = APP_PATH.'http/HttpRpc.php';
        if (file_exists($dir)) return false;

        return copy(BUILT_PATH.'/rpc/http/HttpRpc.php', $dir);
    }

    /**
     * 复制视图到指定位置
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @return bool
    */
    private function copy_view() {
        $dir = APP_PATH.'view'.DS.'HttpRpc'.DS;
        if (file_exists($dir)) return false;
        mkdir($dir, 0755);

        $file = BUILT_PATH.'rpc'.DS.'view'.DS;
        $temp = scandir($file);
        //遍历文件夹
        foreach ($temp as $v){
            if ($v=='.' || $v=='..') continue;
            copy($file.$v, $dir.$v);
        }

        return true;
    }

}