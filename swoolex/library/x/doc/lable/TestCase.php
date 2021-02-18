<?php
// +----------------------------------------------------------------------
// | 单元测试解析类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\doc\lable;
use \x\doc\lable\Basics;

class TestCase extends Basics
{
    /**
     * 启动项
     * @todo 无
     * @author 小黄牛
     * @version v1.2.17 + 2020.10.29
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由参数
     * @param string $request_uri 路由地址
     * @return true
    */
    public function run($route, $request_uri){
        // HTTP请求
        if (!empty($this->request->server['request_method'])) {
            if ($this->request->server['request_method'] == 'GET') {
                $param = $this->request->get;
            } else {
                $param = $this->request->post;
            }
            // 收到了单元测试发起请求
            if (!empty($param['SwooleXTestCase']) && $param['SwooleXTestCase'] == 1) {
                // 没有单元测试用例
                if (empty($route['own']['TestCase'])) {
                    return $this->testcase_callback('该路由，暂未定义单元测试用例'.PHP_EOL);
                }
                
                $TestCase = $route['own']['TestCase'];
                
                $starttime = explode(' ',microtime());

                $ret = [];
                // 循环发起单元测试调试
                foreach ($TestCase as $v) {
                    $v['class'] = str_replace('/', '\\', $v['class']);
                    $obj = new $v['class'];
                    $data = $obj->getData() ?? [];
                    $headers = $obj->getHeaders() ?? [];
                    // 这里不够好，没有判断有没有发起测试成功
                    $data['SwooleXTestCaseClass'] = $v['class'];
                    $v['body'] = $this->http_test_case($request_uri, $route, $data, $headers);
                    $ret[] = $v;
                }
                $endtime = explode(' ',microtime());
                $thistime = round(($endtime[0]+$endtime[1]-($starttime[0]+$starttime[1])), 6);

                // 停止单元测试
                $tips = '测试结束'.PHP_EOL;
                $tips .= '耗时：'.$thistime.' 秒'.PHP_EOL;
                foreach ($ret as $k=>$v) {
                    $tips .= '用例'.($k+1).'：'.$v['class'].'，';
                    if (!empty($v['title'])) $tips .= $v['title'];
                    $tips .= PHP_EOL;
                    $tips .= '执行结果：'.$v['body'].PHP_EOL.PHP_EOL;
                }

                return $this->testcase_callback($tips);
            // 收到了单元测试调试请求
            } else if (!empty($param['SwooleXTestCase']) && $param['SwooleXTestCase'] == 2) {
                $obj = new $param['SwooleXTestCaseClass'];
                // 写入测试用例给DB
                \x\Container::set('testcase', $obj);
            }
        }

        // 更新容器
        return $this->_return();
    }

    /**
     * 单元测试调试-单条-HTTP
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @param array $route 路由信息
     * @param array $data 请求体
     * @param array $headers 请求头
     * @return void
    */
    private function http_test_case($request_uri, $route, $data=[], $headers=[]) {
        $type = strtolower($route['method']);
        $url = '127.0.0.1:'.\x\Config::get('server.port').'/'.ltrim($request_uri);
        $data['SwooleXTestCase'] = 2;

        if ($type == 'get') {
            $url .= '?'.http_build_query($data);
        }

        // 这里什么都不用做，直接触发一次路由就行，请求里代个触发参数
        $curl = curl_init();  
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if ($type == 'post') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if(!empty($headers)){
            $header = [];
            foreach ($headers as $k=>$v) {
                $header[] = $k.':'.$v;
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        // 单位 秒
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);        
        $body = curl_exec($curl);
        curl_close($curl);
        
        return $body;
    }

    /**
     * 回调的处理函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $tips 内容
     * @return void
    */
    protected function testcase_callback($tips) {
        $obj = new \other\lifecycle\testcase_callback();
        $obj->run($tips);
        return false;
    }
}