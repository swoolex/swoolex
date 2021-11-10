<?php
/**
 * +----------------------------------------------------------------------
 * 自定义CMD命令 - HTTP行为验证码 组件创建
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

class Havior {
    /**
     * 命令行参数
    */
    private $argv=[];

    /**
     * 调用入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.27
     * @deprecated 暂不启用
     * @global 无
     * @param array $argv
     * @return void
    */
    public function run($argv) {
        $this->argv = $argv;
        if (empty($argv[2])) return AbstractConsole::exit_error(SystemTips::HAVIOR_SERVER_1 . PHP_EOL);
        if ($argv[2] != 'start') return AbstractConsole::exit_error(SystemTips::HAVIOR_SERVER_2 . PHP_EOL);

        $res = $this->copy_controller();
        if ($res) {
            $html  = 'HTTP行为验证码组件安装完成！'.PHP_EOL.PHP_EOL;
            $html .= 'Ajax校验控制器地址为：/app/http/SwHavior.php。'.PHP_EOL;
            $html .= '注意：请勿删除该控制器。'.PHP_EOL;
        } else {
            $html  = 'HTTP行为验证码组件已存在，请勿重复安装！'.PHP_EOL;
        }

        return AbstractConsole::exit_error($html, false);
    }

    /**
     * 复制控制器到指定位置
     * @todo 无
     * @author 小黄牛
     * @version v1.2.22 + 2021.1.7
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function copy_controller() {
        $controller_path = APP_PATH.'http'.DS.'SwHavior.php';

		if (is_file($controller_path)) return false;

		return copy(BUILT_PATH.'install'.DS.'http'.DS.'verify'.DS.'SwHavior.php', $controller_path);
    }

}