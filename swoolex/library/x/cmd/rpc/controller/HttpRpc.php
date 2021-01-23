<?php
// +----------------------------------------------------------------------
// | SwooleX 官方HTTP-RPC控制台
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace app\controller;
use x\Controller;

/**
 * @Controller(prefix="HttpRpc")
*/
class HttpRpc extends Controller
{
    /**
     * @RequestMapping(route="/login", method="get", title="HTTP-RPC控制台登录页")
    */
    public function login() {
        return $this->display();
    }
    
    /**
     * @RequestMapping(route="/create", method="get", title="HTTP-RPC添加节点")
    */
    public function create() {
        if (!\x\Session::get('httprpc')) {
            return $this->display('HttpRpc/error'); 
        }

        $param = \x\Request::get();

        if (!empty($param['class'])) {
            $list = \x\Rpc::run()->get($param['class']);
            $list = $list[$param['function']];
            $arr = [];
            foreach ($list as $v) {
                if ($v['title'] == $param['title']) {
                    $arr = $v;
                    break;
                }
            }
            if (!empty($arr)) {
                $arr['class'] = $param['class'];
                $arr['function'] = $param['function'];
                $this->assign('info', $arr);
            }
        }
        
        return $this->display();
    }

    /**
     * @RequestMapping(route="/create_ajax", method="post", title="HTTP-RPC添加节点处理")
    */
    public function create_ajax() {
        $param = \x\Request::post();
        if (empty($param['class'])) return $this->returnJson('01', '请先输入路由地址');
        if (empty($param['function'])) return $this->returnJson('01', '请先输入接口名称');
        if (empty($param['title'])) return $this->returnJson('01', '请先输入节点名称');
        if (empty($param['ip'])) return $this->returnJson('01', '请先输入TCP-IP');
        if (empty($param['port'])) return $this->returnJson('01', '请先输入端口');

        $data = [
            'title' => $param['title'],
            'ip' => $param['ip'],
            'port' => $param['port'],
            'score' => $param['score'],
            'status' => $param['status'],
        ];

        $list = \x\Rpc::run()->get();
        if (isset($list[$param['class']][$param['function']])) {
            $arr = $list[$param['class']][$param['function']];
            foreach ($arr as $v) {
                if ($v['title'] == $param['title']) {
                    return $this->returnJson('01', '该节点名称已被使用');
                }
            }
        }
        $list[$param['class']][$param['function']][] = $data;

        $redis_key = \x\Config::run()->get('rpc.redis_key');
        $redis = new \x\Redis();
        $redis->set($redis_key, json_encode($list, JSON_UNESCAPED_UNICODE));
        $redis->return();

        $this->save_map();
        return $this->returnJson('00', '添加成功');
    }

    /**
     * @RequestMapping(route="/update", method="get", title="HTTP-RPC编辑节点")
    */
    public function update() {
        if (!\x\Session::get('httprpc')) {
            return $this->display('HttpRpc/error'); 
        }

        $param = \x\Request::get();

        $list = \x\Rpc::run()->get($param['class']);
        $list = $list[$param['function']];
        $arr = [];
        foreach ($list as $v) {
            if ($v['title'] == $param['title']) {
                $arr = $v;
                break;
            }
        }
        if (!empty($arr)) {
            $arr['class'] = $param['class'];
            $arr['function'] = $param['function'];
            $this->assign('info', $arr);
            return $this->display();
        }

        return $this->fetch('节点不存在');
    }

    /**
     * @RequestMapping(route="/update_ajax", method="post", title="HTTP-RPC编辑节点处理")
    */
    public function update_ajax() {
        $param = \x\Request::post();
        if (empty($param['class'])) return $this->returnJson('01', '请先输入路由地址');
        if (empty($param['function'])) return $this->returnJson('01', '请先输入接口名称');
        if (empty($param['title'])) return $this->returnJson('01', '请先输入节点名称');
        if (empty($param['ip'])) return $this->returnJson('01', '请先输入TCP-IP');
        if (empty($param['port'])) return $this->returnJson('01', '请先输入端口');

        $list = \x\Rpc::run()->get($param['class']);
        $list = $list[$param['function']];

        $arr = [];
        foreach ($list as $v) {
            if ($v['title'] == $param['title']) {
                $arr = $v;
                break;
            }
        }
        
        if (!empty($arr)) {
            $arr['title'] = $param['title'];
            $arr['ip'] = $param['ip'];
            $arr['port'] = $param['port'];
            $arr['score'] = $param['score'];
            $arr['status'] = $param['status'];

            \x\Rpc::run()->setOne($param['class'], $param['function'], $arr);
            $this->save_map();
            return $this->returnJson('00', '修改成功');
        }
        return $this->returnJson('01', '节点不存在！');
    }

    /**
     * @RequestMapping(route="/out", method="get", title="HTTP-RPC退出登陆")
    */
    public function out() {
        \x\Session::delete('httprpc');
        return $this->returnJson('00', '退出成功');
    }

    /**
     * @RequestMapping(route="/login_send", method="post", title="HTTP-RPC登录处理")
    */
    public function send() {
        $param = \x\Request::post();
        if (empty($param['username'])) return $this->returnJson('01', '账号或密码错误');
        if (empty($param['password'])) return $this->returnJson('01', '账号或密码错误');

        $httpClient = new \x\Client();
        $res = $httpClient->http()
                ->domain('https://blog.junphp.com/api/geetest_captcha/php/v1/ajax_vif.php')
                ->body([
                    'appid' => 'blog.junphp.com',
                    'junphp_session_id' => $param['junphp_session_id'],
                    'junphp_appkey' => $param['junphp_appkey'],
                    'junphp_sign' => $param['junphp_sign'],
                    'junphp_time' => $param['junphp_time'],
                    'junphp_geetest' => $param['junphp_geetest'],
                    'junphp_yes' => $param['junphp_yes'],
                ])
                ->post();
        $arr = json_decode($res, true);
        if ($arr['code'] != '00') {
            return $this->fetch($res);
        }

        if ($param['username'] != \x\Config::run()->get('rpc.http_rpc_username')) return $this->returnJson('01', '账号或密码错误');
        if ($param['password'] != \x\Config::run()->get('rpc.http_rpc_password')) return $this->returnJson('01', '账号或密码错误'); 

        \x\Session::set('httprpc', '1');

        $url = '/HttpRpc/index';
        return $this->returnJson('00', '登录成功', $url);
    }

    /**
     * @RequestMapping(route="/index", method="get", title="HTTP-RPC台主页")
    */
    public function index() {
        if (!\x\Session::get('httprpc')) {
            return $this->display('HttpRpc/error'); 
        }
        $param = \x\Request::get();
        // 查询日期
        $param['date'] = $param['date'] ?? date('Y-m-d');
        // 当前分页数
        $page = $param['page'] ?? 1;
        
        // 读取全部RPC服务
        $list = \x\Rpc::run()->get();
        $arr = [];
        foreach ($list as $k=>$v) {
            foreach ($v as $kk=>$vv) {
                foreach ($vv as $val) {
                    $data = $val;
                    $data['class'] = $k;
                    $data['function'] = $kk;
                    $data['url'] = $k.'->'.$kk.'()';
                    $arr[] = $data;
                }
            }
        }

        $this->assign('param', $param);
        $this->assign('arr', $arr);
        return $this->display();
    }
    
    /**
     * @RequestMapping(route="/update_status", method="post", title="HTTP-RPC状态切换")
    */
    public function update_status() {
        $param = \x\Request::post();
        $list = \x\Rpc::run()->get($param['class']);
        $list = $list[$param['function']];
        $arr = [];
        foreach ($list as $v) {
            if ($v['title'] == $param['title']) {
                $arr = $v;
                break;
            }
        }
        if (!empty($arr)) {
            $arr['status'] = $param['status'];
            \x\Rpc::run()->setOne($param['class'], $param['function'], $arr);
            $this->save_map();
            return $this->returnJson('00', '状态修改成功');
        }
        return $this->returnJson('01', '节点不存在！');
    }

    /**
     * @RequestMapping(route="/delete", method="post", title="HTTP-RPC节点删除")
    */
    public function delete() {
        $param = \x\Request::post();
        $list = \x\Rpc::run()->get($param['class']);
        $list = $list[$param['function']];
        $arr = [];
        foreach ($list as $v) {
            if ($v['title'] == $param['title']) {
                $arr = $v;
                break;
            }
        }
        if (!empty($arr)) {
            $res = \x\Rpc::run()->deleteOne($param['class'], $param['function'], $arr);
            if ($res) return $this->returnJson('00', '节点删除成功');
        }
        return $this->returnJson('01', '节点不存在！');
    }
    
    //------------------------------------ 助手函数 ---------------------------------------

    /**
     * 更新到本地的map配置里，防止重启之后，服务没更新
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function save_map() {
        $arr = var_export(\x\Rpc::run()->get(), true);
$html = '<?php
// +----------------------------------------------------------------------
// | 客户端-微服务配置
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

return '.$arr.';';

        $file = ROOT_PATH.'/rpc/map.php';
        \Swoole\Coroutine\System::writeFile($file, $html);
    }

    /**
     * 输出Json到页面
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.14
     * @deprecated 暂不启用
     * @global 无
     * @param string $status
     * @param string $msg
     * @param array $data
     * @return void
    */
    public function returnJson($status, $msg='Success', $data=[]) {
        $json = json_encode([
            'code' => "{$status}",
            'msg' => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE);
        return $this->fetch($json);
    }
}