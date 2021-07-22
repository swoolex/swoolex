<?php
/**
 * +----------------------------------------------------------------------
 * 自定义CMD命令 - controller控制器文件初始化创建
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

class Controller {
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
        if (empty($argv[2])) return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_1 . PHP_EOL);
        if ($argv[2] != 'http' && $argv[2] != 'websocket') return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_2 . PHP_EOL);

        if (empty($argv[3])) return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_3 . PHP_EOL);
        if ($argv[3] == '/') return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_4 . PHP_EOL);
            
        $route_url = rtrim(ltrim($argv[3], '/'), '/');

        $config = \x\Config::get('server');
        $array = json_decode(file_get_contents($config['route_file']), true);
        $route_list = $array[$argv[2]];
        if (isset($route_list[$route_url])) return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_5 . PHP_EOL);

        $ext = explode('/', $route_url);
        $end = end($ext);
        if (stripos($route_url, '/') !== false) {
            $route_url = str_replace('/'.$end, '', $route_url);
            $end = '/'.ucfirst($end).'.php';
        } else {
            $route_url = '';
            $end = ucfirst($end).'.php';
        }
        

        if ($argv[2] == 'http') {
            $make = 'http';
        } else {
            $make = 'websocket';
        }
        
        $controller_file = APP_PATH.$make.DS.$route_url;
        if (!file_exists($controller_file)) {
            @mkdir($controller_file, 0777, true);
        }
        if (!file_exists($controller_file)) return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_6 . PHP_EOL);
        
        if ($argv[2] == 'http') {
            // 需要先创建开箱目录
            \x\common\Unpacking::create_app();
            \x\common\Unpacking::unpack_http(false);
            
            $this->create_http($controller_file.$end, $route_url);
        } else {
            // 需要先创建开箱目录
            \x\common\Unpacking::create_app();
            \x\common\Unpacking::unpack_websocket(false);

            $this->create_websocket($controller_file.$end, $route_url);
        }
    }

    /**
     * 创建HTTP服务控制器
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $file 文件地址
     * @param string $route_url 命名空间地址
     * @return void
    */
    private function create_http($file, $route_url) {
        if ($route_url) {
            $route_url = '\\'.str_replace('/', '\\', $route_url);
        }
        // 不存在就直接创建一个就行
        if (!file_exists($file)) {
            $myfile = fopen($file, "w") or return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_7 . PHP_EOL);
$html = '<?php
// +----------------------------------------------------------------------
// | 示例控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://www.sw-x.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\http'.$route_url.';
use x\controller\Http;

class Index extends Http
{
	/**
	 * @RequestMapping(route="/'.$this->argv[3].'", method="GET", title="'.($this->argv[5]??'').'")
	 * @Ioc(class="\x\Db", name="Db")
	*/
	public function '.($this->argv[5]??'run').'() {
        // 记得归还数据库连接噢
        $this->Db->return();

		return $this->display();
    }
    
}
';
            fwrite($myfile, $html);
            fclose($myfile);
        } else {
            return AbstractConsole::exit_error('['.$file.']' . SystemTips::HTTP_CONTROLLER_8 . PHP_EOL);
        }

        return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_9 . PHP_EOL);
    }

    
    /**
     * 创建WebSocket服务控制器
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param string $file 文件地址
     * @param string $route_url 命名空间地址
     * @return void
    */
    private function create_websocket($file, $route_url) {
        if ($route_url) {
            $route_url = '\\'.str_replace('/', '\\', $route_url);
        }
        // 不存在就直接创建一个就行
        if (!file_exists($file)) {
            $myfile = fopen($file, "w") or return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_7 . PHP_EOL);
$html = '<?php
// +----------------------------------------------------------------------
// | 示例控制器
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://www.sw-x.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\websocket'.$route_url.';
use x\controller\WebSocket;

class Index extends WebSocket
{
	/**
	 * @RequestMapping(route="/'.$this->argv[3].'", title="'.($this->argv[5]??'').'")
	 * @Ioc(class="\x\Db", name="Db")
	*/
	public function '.($this->argv[5]??'run').'() {
        // 记得归还数据库连接噢
        $this->Db->return();

		return $this->fetch(200, "描述", []);
    }
    
}
';
            fwrite($myfile, $html);
            fclose($myfile);
        } else {
            return AbstractConsole::exit_error('['.$file.']' . SystemTips::HTTP_CONTROLLER_8 . PHP_EOL);
        }

        return AbstractConsole::exit_error(SystemTips::HTTP_CONTROLLER_9 . PHP_EOL);
    }

    
}