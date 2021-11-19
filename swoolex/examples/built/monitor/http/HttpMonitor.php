<?php
/**
 * +----------------------------------------------------------------------
 * SwooleX 官方HTTP监控控制台
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
 * @Controller(prefix="HttpMonitor")
*/
class HttpMonitor extends Http {
    private function vif() {
        if (!\x\Session::get('httpmonitor')) {
            return $this->display('HttpMonitor/error'); 
        }
        return true;
    }
    
    /**
     * @RequestMapping(route="/login", method="get", title="HTTP监控控制台登录页")
    */
    public function login() {
        return $this->display();
    }

    /**
     * @RequestMapping(route="/login_send", method="post", title="HTTP监控登录处理")
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

        if ($param['username'] != \x\Config::get('server.http_monitor_username')) return $this->returnJson('01', '账号或密码错误');
        if ($param['password'] != \x\Config::get('server.http_monitor_password')) return $this->returnJson('01', '账号或密码错误'); 

        if (is_dir(\x\Config::get('server.http_monitor_dir_root')) == false) return $this->returnJson('01', '暂未生成任何监控文件');
        
        \x\Session::set('httpmonitor', 1);
        
        $url = '/HttpMonitor/index';
        
        return $this->returnJson('00', '登录成功', $url);
    }
    
    /**
     * @RequestMapping(route="/out", method="get", title="退出登陆")
    */
    public function out() {
        \x\Session::delete('httpmonitor');
        return $this->returnJson('00', '退出成功');
    }
    /**
     * @RequestMapping(route="/index", method="get", title="HTTP监控台主页")
    */
    public function index() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        // 查询日期
        $param['date'] = $param['date'] ?? date('Y-m-d');
        // 当前分页数
        $page = $param['page'] ?? 1;
        
        $root = \x\Config::get('server.http_monitor_dir_root').date('Y_m_d', strtotime($param['date'])).'/';
        // 记录长度
        if (!empty($param['status'])) {
            if ($param['status'] == 1) {
                $dir = $root.'open.json';
            } else {
                $dir = $root.'close.json';
            }
        } else if (!empty($param['route'])) {
            $dir = $root.'route/'.md5($param['route']).'.json';
        } else {
            $dir = $root.'log.json';
        }
        // 获取所有日志列表
        $list = explode('|', \Swoole\Coroutine\System::readFile($dir));
        array_pop($list);
        $total = count($list);
        // 生成分页URL
        $page_url = $this->page_rul($param);
        $this->assign('page', $this->ArrayPage($total, 10, $page_url, $page));
        $this->assign('param', $param);

        // 日志数据分页
        $this->assign('list', $this->get_list($root, $list, $page, 10));

        return $this->display();
    }

    
    /**
     * @RequestMapping(route="/details", method="get", title="HTTP监控记录详情")
    */
    public function details() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $file = \x\Config::get('server.http_monitor_dir_root').$param['file'];
        $array = json_decode(\Swoole\Coroutine\System::readFile($file), true);
        
        $this->assign('file', $file);
        $this->assign('info', $array);

        return $this->display();
    }

    //------------------------------------ 助手函数 ---------------------------------------
    // 生成分页HTML
    function ArrayPage($total, $limit, $url, $page){
        $total = ceil($total / $limit);//总页数
        $html ='<ul class="pagination">';

        # 先来接个分页头
        if ($page > 1) {
            $html .= "<li class='prev'><a href='".$url."&page=1'>首页</a></li>";
            $html .= "<li class='prev'><a href='".$url."&page=".($page-1)."'>前一页</a></li>";
        }

        # 第一种场景
        if ($page <= 4) {
            if ($total >= 9 ) {
                $num = 9;
            }else{
                $num = $total;
            }
            for ($i=1; $i <= $num; $i++) { 
                if ($i==$page) {
                    $html .= "<li class='active'><span>$i</span></li>";
                } else{
                    $html .= "<li><a href='".$url."&page=$i'>$i</a></li>";
                }
            }
        } else {
            # 第三种场景，当前分页数大于等于5，并且就算加5页也要小于等于总页数
            if ($page >= 5 && (($page+5) <= $total) ) {
                # 先计算出左边分页数
                $left  = $page - 3;
                $key   = $page - 1;
                for ($i=$left; $i <= $key; $i++) { 
                    $html .= "<li><a href='".$url."&page=$i'>$i</a></li>";
                }
                # 合并中间分页数
                $html .= "<li class='active'><span>$i</span></li>";
                # 再计算出右边分页数
                $right = $page + 3;
                $key   = $page + 1;
                for ($i=$key; $i <= $right; $i++) { 
                    $html .= "<li><a href='".$url."&page=$i'>$i</a></li>";
                }
            # 第二种场景
            } else {
                $key = $total - 5;
                for ($i=$key; $i <= $total; $i++) { 
                    if ($i==$page) {
                        $html .= "<li class='active'><span>$i</span></li>";
                    } else{
                        $html .= "<li><a href='".$url."&page=$i'>$i</a></li>";
                    }
                }
            }
        }

        # 最后接个分页尾
        if ($page != $total) {
            $html .= "<li class='next'><a href='".$url."&page=".($page+1)."'>后一页</a></li>";
            $html .= "<li class='next'><a href='".$url."&page=".$total."'>尾页</a></li>";
        }

        return $html.'</ul>';
    }

    // 生成分页URL
    private function page_rul($param) {
        $url = '/HttpMonitor/index?s=1';
        if (!empty($param['status'])) $url .= '&status='.$param['status'];
        if (!empty($param['route'])) $url .= '&route='.$param['route'];
        if (!empty($param['date'])) $url .= '&date='.$param['date'];
        return $url;
    }

    // 读取目录下的文件数量
    private function get_list($root, $list, $page, $size) {
        rsort($list);
        $arr = [];
        for($i = 0; $i < $size; $i++){
            $key = ($page - 1) * $size + $i;
            if (!empty($list[$key])) $arr[] = $root.'log/'.$list[$key];
        }

        $root = \x\Config::get('server.http_monitor_dir_root');
        $list = [];
        sort($arr);
        foreach ($arr as $v) {
            $array = json_decode(\Swoole\Coroutine\System::readFile($v), true);
            $array['file'] = str_replace($root, '', $v);
            $list[] = $array;
        }
        
        return $list;
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