<?php
/**
 * +----------------------------------------------------------------------
 * SwooleX 官方HTTP-RPC控制台
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
 * @Controller(prefix="HttpRpc")
*/
class HttpRpc extends Http {
    private function vif() {
        if (!\x\Session::get('httprpc')) {
            return $this->display('HttpRpc/error'); 
        }
        $this->assign('auth', \x\Session::get('httprpc'));
        return true;
    }
    /**
     * @RequestMapping(route="/login", method="get", title="HTTP-RPC控制台登录页")
    */
    public function login() {
        return $this->display();
    }
    
    /**
     * @RequestMapping(route="/debug_send", method="post", title="HTTP-RPC调试发送")
    */
    public function debug_send() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;

        $param = \x\Request::post();

        $body = [];
        $headers = [];
        if (!empty($param['param'])) {
            foreach ($param['param'] as $v) {
                $body[$v['keys']] = $v['values'];
                if (!empty($v['list'])) {
                    $data = [];
                    foreach ($v['list'] as $vv) {
                        $data[$vv['keys']] = $vv['values'];
                    }
                    $body[$v['keys']] = $data;
                }
            }
        }
        if (!empty($param['headers'])) {
            foreach ($param['headers'] as $v) {
                $headers[$v['keys']] = $v['values'];
            }
        }

        $stime = microtime(true);
        $Rpc = new \x\RpcClient();
        $res = $Rpc->route($param['class'])
                ->func($param['function'])
                ->header($headers)
                ->param($body)
                ->max(3)
                ->send();
        $etime = microtime(true);

        return $this->returnJson('00', '请求完成', [
            'time' => '耗时：'.number_format(($etime-$stime), 10, '.', '').' Seconds',
            'data' => dd($Rpc->getMsg()).dd($Rpc->getCode()).dd($res)
        ]);
    }

    /**
     * @RequestMapping(route="/debug_save", method="post", title="HTTP-RPC保存参数文档")
    */
    public function debug_save() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;

        $param = \x\Request::post();

        $redis_key = \x\Config::get('rpc.redis_key').'_doc_'.md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $redis = new \x\Redis();
        $data = [
            'param' => $param['param']??[],
            'headers' => $param['headers']??[],
        ];
        $redis->set($redis_key, json_encode($data, JSON_UNESCAPED_UNICODE));
        $redis->return();
        
        return $this->returnJson('00', '保存成功');
    }

    /**
     * @RequestMapping(route="/debug", method="get", title="HTTP-RPC调试节点")
    */
    public function debug() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;

        $param = \x\Request::get();
        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $redis_key = \x\Config::get('rpc.redis_key').'_hash_'.$md5;
        $redis = new \x\Redis();
        $info = $redis->hGetAll($redis_key);
        $this->assign('info', $info);

        $redis_key = \x\Config::get('rpc.redis_key').'_doc_'.$md5;
        $json = $redis->get($redis_key);
        if ($json) {
            $info = json_decode($json, true);
            $this->assign('param', $info['param']);
            $this->assign('headers', $info['headers']);
        } else {
            $this->assign('param', []);
            $this->assign('headers', []);
        }

        $redis->return();
        return $this->display();
    }
    
    /**
     * @RequestMapping(route="/create", method="get", title="HTTP-RPC添加节点")
    */
    public function create() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;

        $param = \x\Request::get();

        if (!empty($param['class'])) {
            $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
            $redis_key = \x\Config::get('rpc.redis_key').'_hash_'.$md5;
            $redis = new \x\Redis();
            $info = $redis->hGetAll($redis_key);

            $redis_key = \x\Config::get('rpc.redis_key');
            $list = $redis->hKeys($redis_key.'_item');
            $this->assign('item', $list);
            $list = $redis->hKeys($redis_key.'_group');
            $this->assign('group', $list);

            $redis->return();
            $this->assign('info', $info);
        } else {
            $redis = new \x\Redis();
            
            $redis_key = \x\Config::get('rpc.redis_key');
            $list = $redis->hKeys($redis_key.'_item');
            $this->assign('item', $list);
            $list = $redis->hKeys($redis_key.'_group');
            $this->assign('group', $list);

            $redis->return();
        }
        
        return $this->display();
    }

    /**
     * @RequestMapping(route="/create_ajax", method="post", title="HTTP-RPC添加节点处理")
    */
    public function create_ajax() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::post();
        if (empty($param['class'])) return $this->returnJson('01', '请先输入路由地址');
        if (empty($param['function'])) return $this->returnJson('01', '请先输入接口名称');
        if (empty($param['title'])) return $this->returnJson('01', '请先输入节点名称');
        if (empty($param['ip'])) return $this->returnJson('01', '请先输入TCP-IP');
        if (empty($param['port'])) return $this->returnJson('01', '请先输入端口');
        

        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();
        
        // 判断节点是否存在
        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $hash_key = '_hash_'.$md5;
        $info = $redis->hGetAll($redis_key.$hash_key);
        if (!empty($info)) {
            $redis->return();
            return $this->returnJson('01', '改节点已存在，请修改后重新提交');
        }
        // 创建节点
        $sets_key = '_sets_'.md5($param['class'].$param['function']);
        // 记录key名
        $redis->SADD($redis_key.$sets_key, $hash_key);
        // 记录节点详情
        $data = [
            'class' => $param['class'],
            'function' => $param['function'],
            'title' => $param['title'],
            'ip' => $param['ip'],
            'port' => $param['port'],
            'item' => $param['item'],
            'group' => $param['group'],
            'max_ms' => $param['max_ms'],
            'route_minute' => $param['route_minute'],
            'route_limit' => $param['route_limit'],
            'ip_minute' => $param['ip_minute'],
            'ip_limit' => $param['ip_limit'],
            'is_fault' => 0,
            'status' => 0,
        ];

        $redis->HMSET($redis_key.$hash_key, $data);

        $score_key = '_score_'.$md5;
        $peaks_key = '_peaks_'.$md5;
        $num_key = '_num_'.$md5;
        // 初始化
        $redis->set($redis_key.$score_key, 100);
        $redis->set($redis_key.$peaks_key, 0);
        $redis->set($redis_key.$num_key, 0);
        $redis->LPUSH($redis_key, $sets_key);
        $redis->LPUSH($redis_key, $hash_key);
        $redis->LPUSH($redis_key, $score_key);
        $redis->LPUSH($redis_key, $peaks_key);
        $redis->LPUSH($redis_key, $num_key);
        $redis->return();

        $this->save_map();
        return $this->returnJson('00', '添加成功');
    }

    /**
     * @RequestMapping(route="/update", method="get", title="HTTP-RPC编辑节点")
    */
    public function update() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;

        $param = \x\Request::get();

        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $redis_key = \x\Config::get('rpc.redis_key').'_hash_'.$md5;
        $redis = new \x\Redis();
        $info = $redis->hGetAll($redis_key);

        $redis_key = \x\Config::get('rpc.redis_key');
        $list = $redis->hKeys($redis_key.'_item');
        $this->assign('item', $list);
        $list = $redis->hKeys($redis_key.'_group');
        $this->assign('group', $list);

        $redis->return();

        $this->assign('info', $info);
        return $this->display();
    }

    /**
     * @RequestMapping(route="/update_ajax", method="post", title="HTTP-RPC编辑节点处理")
    */
    public function update_ajax() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::post();
        if (empty($param['class'])) return $this->returnJson('01', '请先输入路由地址');
        if (empty($param['function'])) return $this->returnJson('01', '请先输入接口名称');
        if (empty($param['title'])) return $this->returnJson('01', '请先输入节点名称');
        if (empty($param['ip'])) return $this->returnJson('01', '请先输入TCP-IP');
        if (empty($param['port'])) return $this->returnJson('01', '请先输入端口');
        $data = [
            'class' => $param['class'],
            'function' => $param['function'],
            'title' => $param['title'],
            'ip' => $param['ip'],
            'port' => $param['port'],
            'item' => $param['item'],
            'group' => $param['group'],
            'route_minute' => $param['route_minute'],
            'route_limit' => $param['route_limit'],
            'ip_minute' => $param['ip_minute'],
            'ip_limit' => $param['ip_limit'],
            'is_fault' => 0,
            'status' => 0,
        ];
        // 找出原来的key
        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();
        // 修改了路由地址
        if (
            $param['start_class'] != $param['class'] || 
            $param['start_function'] != $param['function'] || 
            $param['start_ip'] != $param['ip'] || 
            $param['start_port'] != $param['port']
        ) {
            // 查询节点是否已存在
            $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
            $hash_key = '_hash_'.$md5;
            $info = $redis->hGetAll($redis_key.$hash_key);
            if (!empty($info)) {
                $redis->return();
                return $this->returnJson('01', '改节点已存在，不允许跨节点覆盖修改');
            }
            // 删除原节点
            $md5 = md5($param['start_class'].$param['start_function'].$param['start_ip'].$param['start_port']);
            $hash_key = '_hash_'.$md5;
            $sets_key = '_sets_'.md5($param['start_class'].$param['start_function']);
            $redis->SREM($redis_key.$sets_key, $hash_key);
            $score_key = '_score_'.$md5;
            $peaks_key = '_peaks_'.$md5;
            $num_key = '_num_'.$md5;
            $redis->DEL($redis_key.$hash_key);
            $redis->DEL($redis_key.$score_key);
            $redis->DEL($redis_key.$peaks_key);
            $redis->DEL($redis_key.$num_key);
            // 创建新节点
            $sets_key = '_sets_'.md5($param['class'].$param['function']);
            $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
            $hash_key = '_hash_'.$md5;
            // 记录key名
            $redis->SADD($redis_key.$sets_key, $hash_key);
            // 记录节点详情
            $redis->HMSET($redis_key.$hash_key, $data);

            $score_key = '_score_'.$md5;
            $peaks_key = '_peaks_'.$md5;
            $num_key = '_num_'.$md5;
            // 初始化
            $redis->set($redis_key.$score_key, 100);
            $redis->set($redis_key.$peaks_key, 0);
            $redis->set($redis_key.$num_key, 0);
            $redis->LPUSH($redis_key, $sets_key);
            $redis->LPUSH($redis_key, $hash_key);
            $redis->LPUSH($redis_key, $score_key);
            $redis->LPUSH($redis_key, $peaks_key);
            $redis->LPUSH($redis_key, $num_key);
        } else {
            unset($param['start_class']);
            unset($param['start_function']);
            unset($param['start_ip']);
            unset($param['start_port']);
            \x\Rpc::run()->set($param);
        }
        $redis->return();
        
        $this->save_map();
        return $this->returnJson('00', '修改成功');
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
        $list = \x\Config::get('rpc.http_rpc_user_list');
        $status = false;
        foreach ($list as $v) {
            if ($param['username'] == $v['username'] && $param['password'] == $v['password']) {
                $status = $v['auth'];
                break;
            }
        }
        if ($status == false) return $this->returnJson('01', '账号或密码错误');

        \x\Session::set('httprpc', $status);

        $url = '/HttpRpc/index';
        return $this->returnJson('00', '登录成功', $url);
    }

    /**
     * @RequestMapping(route="/index", method="get", title="HTTP-RPC台主页")
    */
    public function index() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        $param = \x\Request::get();

        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();

        // 读取全部服务
        $max = $redis->LLEN($redis_key);
        $cache = [];
        $arr = [];
        for ($i=0; $i<$max; $i++) {
            $key = $redis->LINDEX($redis_key, $i);
            if (strpos($key, '_hash_') !== false) {
                if (isset($cache[$key])) {
                    continue;
                } else {
                    $cache[$key] = 1;
                }
                $val = $redis->hGetAll($redis_key.$key);
                if ($val) {
                    $md5 = md5($val['class'].$val['function'].$val['ip'].$val['port']);
                    $score_key = '_score_'.$md5;
                    $peaks_key = '_peaks_'.$md5;
                    $num_key = '_num_'.$md5;
                    $val['ping_ms'] = $redis->get($redis_key.$peaks_key);
                    $val['score'] = $redis->get($redis_key.$score_key);
                    $val['request_num'] = $redis->get($redis_key.$num_key);
                    $val['url'] = $val['class'].'->'.$val['function'].'()';
                    $arr[] = $val;
                }
            }
        }
        // 项目
        if (!empty($param['item'])) {
            foreach ($arr as $k => $v) {
                if (stripos($v['item'], $param['item']) === false) {
                    unset($arr[$k]);
                }
            }
        }
        // 分组
        if (!empty($param['group'])) {
            foreach ($arr as $k => $v) {
                if (stripos($v['group'], $param['group']) === false) {
                    unset($arr[$k]);
                }
            }
        }
        // 标题
        if (!empty($param['title'])) {
            foreach ($arr as $k => $v) {
                if (stripos($v['title'], $param['title']) === false) {
                    unset($arr[$k]);
                }
            }
        }
        // 服务地址
        if (!empty($param['route'])) {
            foreach ($arr as $k => $v) {
                if (stripos($v['url'], $param['route']) === false) {
                    unset($arr[$k]);
                }
            }
        }
        // 状态
        if (!empty($param['status'])) {
            foreach ($arr as $k => $v) {
                switch ($param['status']) {
                    case 1: 
                        if ((isset($v['status']) && $v['status'] == 2) || (isset($v['is_fault']) && $v['is_fault'] == 1)) {
                            unset($arr[$k]);
                        }
                    break;
                    case 2: 
                        if ((isset($v['status']) && $v['status'] == 0)) {
                            unset($arr[$k]);
                        }
                    break;
                    case 3: 
                        if (!isset($v['is_fault']) || (isset($v['is_fault']) && $v['is_fault'] == 0)) {
                            unset($arr[$k]);
                        }
                    break;
                }
            }
        }

        $list = $redis->hKeys($redis_key.'_item');
        $this->assign('item', $list);
        $list = $redis->hKeys($redis_key.'_group');
        $this->assign('group', $list);

        $redis->return();

        $this->assign('param', $param);
        $this->assign('arr', $arr);


        return $this->display();
    }
    
    /**
     * @RequestMapping(route="/update_status", method="post", title="HTTP-RPC状态切换")
    */
    public function update_status() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        $param = \x\Request::post();

        // 先读
        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $redis_key = \x\Config::get('rpc.redis_key').'_hash_'.$md5;
        $redis = new \x\Redis();
        $res = $redis->HMSET($redis_key, [
            'status' => $param['status']
        ]);
        $redis->return();
        
        if ($res !== false) {
            $this->save_map();
            return $this->returnJson('00', '状态修改成功');
        }
        return $this->returnJson('01', '状态修改失败');
    }

    /**
     * @RequestMapping(route="/input_save", method="post", title="HTTP-RPC单个字段修改")
    */
    public function input_save() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        $param = \x\Request::post();

        // 先读
        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $redis_key = \x\Config::get('rpc.redis_key').'_hash_'.$md5;
        $redis = new \x\Redis();
        $res = $redis->HMSET($redis_key, [
            $param['field'] => $param['num']
        ]);
        $redis->return();
        
        if ($res !== false) {
            $this->save_map();
            return $this->returnJson('00', '修改成功');
        }
        return $this->returnJson('01', '修改失败');
    }


    /**
     * @RequestMapping(route="/delete", method="post", title="HTTP-RPC节点删除")
    */
    public function delete() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        $param = \x\Request::post();
        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();
        
        // 删除该条记录
        $md5 = md5($param['class'].$param['function'].$param['ip'].$param['port']);
        $hash_key = '_hash_'.$md5;
        $sets_key = '_sets_'.md5($param['class'].$param['function']);
        $redis->SREM($redis_key.$sets_key, $hash_key);
        $score_key = '_score_'.$md5;
        $peaks_key = '_peaks_'.$md5;
        $num_key = '_num_'.$md5;
        $redis->DEL($redis_key.$hash_key);
        $redis->DEL($redis_key.$score_key);
        $redis->DEL($redis_key.$peaks_key);
        $redis->DEL($redis_key.$num_key);
        $redis->return();

        $this->save_map();
        return $this->returnJson('00', '节点删除成功');
    }

    /**
     * @RequestMapping(route="/error_list", method="get", title="HTTP-RPC错判日志列表")
    */
    public function error_list() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $key = 'err_';
        if (!empty($param['class'])) {
            $key .= str_replace('/', '_', $param['class']);
        }
        if (!empty($param['class']) && !empty($param['function'])) {
            $key .= '|'.$param['function'];
        }
        
        $redis = new \x\Redis();
        $title = $redis->lrange('rpc_err_list', 0, -1);
        $key_list = [];
        foreach ($title as $v) {
            if (stripos($v, $key) !== false) {
                $key_list[] = $v;
            }
        }
        $list = [];
        foreach ($key_list as $key) {
            $list[] = $redis->lrange($key, 0, -1);
        }
        $redis->return(); 

        $this->assign('param', $param);
        $this->assign('list', $list);
        return $this->display();
    }

    /**
     * @RequestMapping(route="/error_delete", method="post", title="HTTP-RPC错误日志删除")
    */
    public function error_delete() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::post();
        $key = 'err_';
        if (!empty($param['class'])) {
            $key .= str_replace('/', '_', $param['class']);
        }
        if (!empty($param['class']) && !empty($param['function'])) {
            $key .= '|'.$param['function'];
        }

        $redis = new \x\Redis();
        // 删除该条记录
        $redis->lset($key, $param['redis_index'], 'swoolex_rpc_delete');
        $res = $redis->lrem($key, 'swoolex_rpc_delete', 0);
        $redis->return();

        if ($res) {
            return $this->returnJson('00', '删除成功');
        }
        return $this->returnJson('01', '删除失败！');
    }

    /**
     * @RequestMapping(route="/repeat", method="post", title="HTTP-RPC错判记录重发")
    */
    public function repeat() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::post();
        $class = $param['class'];
        $function = $param['function'];
        $task = $param['task']??0;
        $callback = $param['callback']??'';
        $callback_type = $param['callback_type']??'post';
        $header = json_decode($param['header'], true);
        $param = json_decode($param['param'], true);

        $starttime = explode(' ',microtime());
        // start
        $Rpc = new \x\RpcClient();
        $Rpc->route($class)
               ->func($function)
               ->header($header)
               ->param($param)
               ->max(3);

        if ($task != '否') {
            $Rpc->task();
        }
        if (!empty($callback)) {
            $Rpc->callback($callback, $callback_type);
        }
        $res = $Rpc->send();
        // end
        $endtime = explode(' ',microtime());
        $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
        $html = '耗时：'.($thistime*1000).'ms<br/>'.dd($res);

        return $this->fetch($html);
    }

    /**
     * @RequestMapping(route="/item", method="get", title="HTTP-RPC项目列表")
    */
    public function item() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();
        $list = $redis->hKeys($redis_key.'_item');
        $redis->return();
        $this->assign('list', $list);
        return $this->display();
    }

    /**
     * @RequestMapping(route="/item_save", method="get", title="HTTP-RPC项目编辑")
    */
    public function item_save() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $redis = new \x\Redis();
        if (empty($param['title'])) {
            return $this->returnJson('01', '请先输入项目名称！');
        }
        $redis_key  = \x\Config::get('rpc.redis_key');
        $redis_key .= '_item';
        // 先删除
        if (!empty($param['id'])) {
            $redis->hDel($redis_key, $param['id']);
        }
        $res = $redis->hSet($redis_key, $param['title'], 1);
        $redis->return();
        if ($res) {
            return $this->returnJson('00', '编辑成功！');
        }
        return $this->returnJson('01', '编辑失败！');
    }

    /**
     * @RequestMapping(route="/item_delete", method="get", title="HTTP-RPC项目删除")
    */
    public function item_delete() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $redis = new \x\Redis();
        $redis_key  = \x\Config::get('rpc.redis_key');
        $redis_key .= '_item';
        $res = $redis->hDel($redis_key, $param['id']);
        $redis->return();
        if ($res) {
            return $this->returnJson('00', '删除成功！');
        }
        return $this->returnJson('01', '删除失败！');
    }

    /**
     * @RequestMapping(route="/group", method="get", title="HTTP-RPC分组列表")
    */
    public function group() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();
        $list = $redis->hKeys($redis_key.'_group');
        $redis->return();
        $this->assign('list', $list);
        return $this->display();
    }

    /**
     * @RequestMapping(route="/group_save", method="get", title="HTTP-RPC分组编辑")
    */
    public function group_save() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $redis = new \x\Redis();
        if (empty($param['title'])) {
            return $this->returnJson('01', '请先输入分组名称！');
        }
        $redis_key  = \x\Config::get('rpc.redis_key');
        $redis_key .= '_group';
        // 先删除
        if (!empty($param['id'])) {
            $redis->hDel($redis_key, $param['id']);
        }
        $res = $redis->hSet($redis_key, $param['title'], 1);
        $redis->return();
        if ($res) {
            return $this->returnJson('00', '编辑成功！');
        }
        return $this->returnJson('01', '编辑失败！');
    }

    /**
     * @RequestMapping(route="/group_delete", method="get", title="HTTP-RPC分组删除")
    */
    public function group_delete() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        
        $param = \x\Request::get();
        $redis = new \x\Redis();
        $redis_key  = \x\Config::get('rpc.redis_key');
        $redis_key .= '_group';
        $res = $redis->hDel($redis_key, $param['id']);
        $redis->return();
        if ($res) {
            return $this->returnJson('00', '删除成功！');
        }
        return $this->returnJson('01', '删除失败！');
    }

    
    /**
     * @RequestMapping(route="/charts", method="get", title="HTTP-RPC请求统计")
    */
    public function charts() {
        $vif = $this->vif();
        if ($vif !== true) return $vif;
        $redis_config = \x\Config::get('rpc');
        $param = \x\Request::get();
        $redis_key = $redis_config['redis_key'].'_sta_'.md5($param['class'].$param['function'].$param['ip'].$param['port']);
        
        $redis = new \x\Redis($redis_config['chat_redis_driver']);
        
        $type = $param['type'] ?? 1;
        if ($type == 1) {
            $time = time();
            $start = strtotime(date('Y-m-d', time()));
        } else {
            $time = strtotime(date('Y-m-d', time()));
            $start = $time-(86400*($type-1));
            $time = $start+86400;
        }
        $this->assign('type', $type);
        
        $list = [];
        for ($i=$start; $i <= $time; $i+=3600) { 
            $list[] = $i;
        }
        $num = count($list)-1;
        $date = [];
        $data = [];
        $max = 10;
        foreach ($list as $k => $v) {
            if ($k == 0) {
                continue;
            }
            $date[] = date('H:i', $v);
            $end = ($v-1);
            $count = $redis->get($redis_key.'_'.date('YmdH', $end));
            if (!$count) $count = 0;
            $data[] = $count;
            if ($max < $count) $max = $count;

            if ($k == $num && $type == 1) {
                $date[] = date('H:i', time());
                $end = time();
                $count = $redis->get($redis_key.'_'.date('YmdH', $v));
                if (!$count) $count = 0;
                $data[] = $count;
                if ($max < $count) $max = $count;
            }
        }
        
        $redis->return();

        // 菜单
        $weekarray = ['日','一','二','三','四','五','六'];
        $menu = [];
        for ($i=1; $i <= $redis_config['chat_days']; $i++) {
            $s = time()-(86400*($i-1));
            switch ($i) {
                case 1:$title='今天';break;
                case 2:$title='昨天';break;
                case 3:$title='前天';break;
                default:
                    $title = date('Y-m-d', $s);
                break;
            }
            $w = date('w', $s);
            $menu[$i] = $title.' (周'.$weekarray[$w].')';
        }
        
        $this->assign('date', $date);
        $this->assign('data', $data);
        $this->assign('param', $param);
        $this->assign('max', $max);
        $this->assign('menu', $menu);
        return $this->display();
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
        $cache = [];
        $list = [];

        $redis_key = \x\Config::get('rpc.redis_key');
        $redis = new \x\Redis();

        // 读取全部服务
        $max = $redis->LLEN($redis_key);
        for ($i=0; $i<$max; $i++) {
            $key = $redis->LINDEX($redis_key, $i);
            if (strpos($key, '_hash_') !== false) {
                if (isset($cache[$key])) {
                    continue;
                } else {
                    $cache[$key] = 1;
                }
                $v = $redis->hGetAll($redis_key.$key);
                if ($v) {
                    $data = $v;
                    unset($data['class']);
                    unset($data['function']);
                    $list[$v['class']][$v['function']][] = $data;
                }
            }
        }
        $redis->return();

        $arr = var_export($list, true);
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