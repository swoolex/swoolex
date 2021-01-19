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

        $url = '/HttpRpc/index?username='.md5(\x\Config::run()->get('rpc.http_rpc_username')).'&password='.md5(\x\Config::run()->get('rpc.http_rpc_password')).'&ip='.\x\Request::ip();
        return $this->returnJson('00', '登录成功', $url);
    }

    /**
     * @RequestMapping(route="/index", method="get", title="HTTP-RPC台主页")
    */
    public function index() {
        $param = \x\Request::get();
        if (empty($param['username'])) return $this->display('HttpRpc/error');
        if (empty($param['password'])) return $this->display('HttpRpc/error');
        if (empty($param['ip'])) return $this->display('HttpRpc/error');
        if ($param['username'] != md5(\x\Config::run()->get('rpc.http_rpc_username'))) return $this->display('HttpRpc/error');
        if ($param['password'] != md5(\x\Config::run()->get('rpc.http_rpc_password'))) return $this->display('HttpRpc/error'); 
        if ($param['ip'] != \x\Request::ip()) return $this->display('HttpRpc/error'); 
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
            return $this->returnJson('00', '状态修改成功');
        }
        return $this->returnJson('01', '节点不存在！');
    }

    //------------------------------------ 助手函数 ---------------------------------------

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