<?php
/**
 * +----------------------------------------------------------------------
 * Es数据库组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;
use x\elasticsearch\tool\Client;
use x\elasticsearch\tool\Request;
use x\elasticsearch\tool\Response;

class Elasticsearch
{
    /**
     * 强制指定使用某个节点
    */
    private $node = null;
    /**
     * 重试多少个节点，默认使用配置文件
    */
    private $retry_max_num = null;

    /**
     * 初始化配置
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param array $node
     * @return this
    */
    public function __construct() {
        $this->node = null;
        $this->retry_max_num = \x\Config::get('elasticsearch.retry_max_num');
        return $this;
    }
    /**
     * 强制指定使用某个节点
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $node
     * @return this
    */
    public function config($node) {
        $this->node = $node;
        return $this;
    }
    /**
     * 指定重试多少个节点
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param int $num
     * @return this
    */
    public function max($num) {
        $this->retry_max_num = $num;
        return $this;
    }
    /**
     * 发送请求
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $url   请求地址
     * @param string $verb  动词
     * @param array $json 请求数据
     * @param array $node 已经请求过的节点信息
     * @param bool $debug 是否调试json
     * @return mixed
    */
    public function exec($url, $verb, $json=[], $node=[], $debug=false) {
        // 超过重试次数
        if (count($node) == $this->retry_max_num) return false;
        if (is_array($json)) {
            if (empty($json)) {
                $body = new \StdClass;
            } else {
                $body = json_encode($json);
            }
        } else {
            $body = $json;
        }
        
        if ($debug == true) return $body;

        // 获得一个节点
        $node_info = \x\elasticsearch\tool\Client::get_node($node);
        // 已无节点可用
        if ($node_info == false) return false;

        // 发送请求
        $ret = Request::handle($url, $verb, $body, $node_info);
        // 发送失败，递归重试
        if ($ret == false) {
            $node[] = $node_info->getHost();
            return $this->exec($url, $verb, $json, $node);
        }

        return Response::handle($ret);
    }

    /**
     * ORM构造器注入
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return class
    */
    public function __call($name, $arguments=[]) {
        return call_user_func_array([new \x\elasticsearch\orm\Builder($this), $name], $arguments);
    }
}