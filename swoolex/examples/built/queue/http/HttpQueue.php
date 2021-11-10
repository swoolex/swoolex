<?php
/**
 * +----------------------------------------------------------------------
 * SwooleX 官方HTTP-Queue控制台
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace app\http;
use x\controller\Http;

/**
 * @Controller(prefix="HttpQueue")
*/
class HttpQueue extends Http {
    private function vif() {
        if (!\x\Session::get('httpqueue')) {
            return $this->display('HttpQueue/login'); 
        }
        return true;
    }
    
    /**
     * @RequestMapping(route="/login", method="get", title="登录页")
    */
    public function login() {
        return $this->display();
    }

    /**
     * @RequestMapping(route="/out", method="get", title="退出登陆")
    */
    public function out() {
        \x\Session::delete('httpqueue');
        return $this->returnJson('00', '退出成功');
    }

    /**
     * @RequestMapping(route="/login_send", method="post", title="登录处理")
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
        $list = \x\Config::get('queue.http_queue_user_list');
        $status = false;
        foreach ($list as $v) {
            if ($param['username'] == $v['username'] && $param['password'] == $v['password']) {
                $status = true;
                break;
            }
        }
        if ($status == false) return $this->returnJson('01', '账号或密码错误');

        \x\Session::set('httpqueue', '1');

        $url = '/HttpRpc/index';
        return $this->returnJson('00', '登录成功', $url);
    }

    /**
     * @RequestMapping(route="/index", method="get", title="主页")
    */
    public function index() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $list = \x\Config::get('queue');
        unset($list['http_queue_user_list']);
        foreach ($list as $k=>$v) {
            $Queue = new \x\Queue($k);
            $list[$k]['confirm'] = $Queue->count('confirm');
            $list[$k]['waiting'] = $Queue->count('waiting');
            $list[$k]['reserved'] = $Queue->count('reserved');
            $list[$k]['delayed'] = $Queue->count('delayed');
            $list[$k]['failed'] = $Queue->count('failed');
            $list[$k]['timeout'] = $Queue->count('timeout');
            $list[$k]['entity'] = $Queue->count('entity');
        }
        $this->assign('list', $list);
        return $this->display(); 
    }
    /**
     * @RequestMapping(route="/showlist", method="get", title="队列列表")
    */
    public function showlist() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $count = $Queue->count($param['type']);
        $page = ($param['page']??1);
        $list = $Queue->page($param['type'], $page, 10);
        $bs = new \x\page\Bootstrap([], 10, $page, $count, [
            'query' => $param
        ]);
        $array = [];
        foreach ($list as $k=>$Job) {
            $array[$k]['node'] = $param['node'];
            $array[$k]['type'] = $param['type'];
            $array[$k]['uuid'] = $Job->uuid();
            $array[$k]['push_time'] = isset($Job->push_time) ? date('Y-m-d H:i:s', $Job->push_time) : '';
            $array[$k]['delay_time'] = $Job->getDelayTime();
            $array[$k]['wait_time'] = $Job->getWaitTime();
            if ($array[$k]['wait_time']) {
                $array[$k]['wait_time'] = date('Y-m-d H:i:s', strtotime($array[$k]['push_time'])+$array[$k]['wait_time']);
            }
            $array[$k]['out_time'] = $Job->getOutTime();
            $array[$k]['retry_seconds'] = $Job->getRetrySeconds();
            $array[$k]['retry_time'] = $Job->retry_time();
        }
        $this->assign('list', $array);
        $this->assign('page', $bs->render());
        switch ($param['type']) {
            case 'confirm':$title = '待确认的队列';break;
            case 'waiting':$title = '等待消费的队列';break;
            case 'reserved':$title = '正在消费的队列';break;
            case 'delayed':$title = '延迟消费的队列';break;
            case 'failed':$title = '消费失败的队列';break;
            case 'timeout':$title = '消费超时的队列';break;
        }
        $this->assign('title', $title);
        
        return $this->display(); 
    }
    
    /**
     * @RequestMapping(route="/clears", method="get", title="清空队列-全部")
    */
    public function clears() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $res = $Queue->clear($param['type']);
        if (!$res) return $this->returnJson('01', '清空失败');

        return $this->returnJson('00', '清空完成');
    }
    /**
     * @RequestMapping(route="/clear", method="get", title="清空队列-单条")
    */
    public function clear() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $res = $Queue->delete($param['type'], $param['uuid']);
        if (!$res) return $this->returnJson('01', '删除失败');

        return $this->returnJson('00', '删除成功');
    }
    /**
     * @RequestMapping(route="/retrys", method="get", title="重试队列-全部")
    */
    public function retrys() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $res = $Queue->moves($param['type']);
        if (!$res) return $this->returnJson('01', '转入失败');

        return $this->returnJson('00', '转入完成');
    }
    /**
     * @RequestMapping(route="/retry", method="get", title="重试队列-单条")
    */
    public function retry() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $res = $Queue->move($param['type'], $param['uuid']);
        if (!$res) return $this->returnJson('01', '转入失败');

        return $this->returnJson('00', '转入完成');
    }
    /**
     * @RequestMapping(route="/initialize", method="get", title="初始化队列")
    */
    public function initialize() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $res = $Queue->initialize();
        if (!$res) return $this->returnJson('01', '初始化失败');

        return $this->returnJson('00', '初始化完成');
    }
    /**
     * @RequestMapping(route="/info", method="get", title="队列详情")
    */
    public function info() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $Queue = new \x\Queue($param['node']);
        $Job = $Queue->info($param['uuid']);
        if (!$Job) return $this->returnJson('01', '获取失败');
        
        return $this->returnJson('00', '获取完成', dd($Job->param()));
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