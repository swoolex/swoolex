<?php
/**
 * +----------------------------------------------------------------------
 * 请求处理
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\tool;

class Request
{
    /**
     * 发送请求
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $url   请求地址
     * @param string $verb  动词
     * @param array $body 请求数据
     * @param array $node 节点信息
     * @return mixed
    */
    public static function handle($url, $verb, $body, $node) {
        $channel = new \Swoole\Coroutine\Channel;
        
        go(function() use($url, $verb, $body, $node, $channel) {
            $url = $node->getHost().$url;
            $node_info = $node->getNode();
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $node->getHeader());
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, \x\Config::get('elasticsearch.client_send_outtime')); // 请求超时时间
            
            // 鉴权
            if (!empty($node_info['user']) && !empty($node_info['user'])) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $node_info['user'].':'.$node_info['pass']);
            }
            // 证书
            if (!empty($node_info['ssl_path'])) {
                $options = [];
                $options[CURLOPT_SSL_VERIFYPEER] = true;
                $options[CURLOPT_CAINFO] = $node_info['ssl_path'];
                $options[CURLOPT_SSL_VERIFYHOST] = 2;
                //忽略证书验证,信任任何证书
                $options[CURLOPT_SSL_VERIFYHOST] = false;
                $options[CURLOPT_SSL_VERIFYPEER] = false;
                curl_setopt_array($ch, $options);
            } else {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }

            $result = curl_exec($ch);
            
            curl_close($ch);
            $channel->push($result);;
        });
        
        $ret = $channel->pop();
        $channel->close();
        
        return $ret;
    }
}
