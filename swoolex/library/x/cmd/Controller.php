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

class Controller
{
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
        if (empty($argv[2])) die('SwooleX-ERROR：sw-x controller missing parameter 1！'.PHP_EOL);
        if ($argv[2] != 'http' && $argv[2] != 'websocket') die('SwooleX-ERROR：sw-x controller [server] error，support only：http、websocket'.PHP_EOL);

        if (empty($argv[3])) die('SwooleX-ERROR：sw-x controller missing parameter 2！'.PHP_EOL);
        if ($argv[3] == '/') die('SwooleX-ERROR：sw-x controller Route not allow /'.PHP_EOL);
            
        $route_url = rtrim(ltrim($argv[3], '/'), '/');

        $config = \x\Config::get('server');
        $array = json_decode(file_get_contents($config['route_file']), true);
        $route_list = $array[$argv[2]];
        if (isset($route_list[$route_url])) die('SwooleX-ERROR：sw-x controller Route already exists！'.PHP_EOL);

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
            $make = 'controller';
        } else {
            $make = 'socket';
        }
        
        $controller_file = ROOT_PATH.'/app/'.$make.'/'.$route_url;
        if (!file_exists($controller_file)) {
            @mkdir($controller_file, 0777, true);
        }
        if (!file_exists($controller_file)) die('SwooleX-ERROR：sw-x controller 没有权限创建路由目录！'.PHP_EOL);
        
        if ($argv[2] == 'http') {
            $this->create_http($controller_file.$end, $route_url);
        } else {
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
            $myfile = fopen($file, "w") or die('SwooleX-ERROR：sw-x controller 没有权限，控制器创建失败！'.PHP_EOL);
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

namespace app\controller'.$route_url.';
use x\Controller;

class Index extends Controller
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
            die('SwooleX-ERROR：sw-x controller ['.$file.']文件已存在！'.PHP_EOL);
        }

        die('SwooleX-ERROR：sw-x controller Create Success！'.PHP_EOL);
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
            $myfile = fopen($file, "w") or die('SwooleX-ERROR：sw-x controller 没有权限，控制器创建失败！'.PHP_EOL);
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

namespace app\socket'.$route_url.';
use x\WebSocket;

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
            die('SwooleX-ERROR：sw-x controller ['.$file.']文件已存在！'.PHP_EOL);
        }

        die('SwooleX-ERROR：sw-x controller Create Success！'.PHP_EOL);
    }

    
}