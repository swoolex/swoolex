<?php
/**
 * +----------------------------------------------------------------------
 * ES-API客户端
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\tool;
use x\elasticsearch\connections\Connection;
use x\elasticsearch\tool\AbstractClient;
use x\elasticsearch\tool\Request;
use x\elasticsearch\tool\Response;
use Exception;

class Client extends AbstractClient
{
    /**
     * SDK版本号
    */
    const VERSION = '1.0.1';

    /**
     * 正常的节点配置
    */
    private static $normal_node = [];
    /**
     * 故障的节点配置
    */
    private static $fault_node = [];
    /**
     * 通用配置信息
    */
    private static $config = [];

    /**
     * 读取配置
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
    */
    public static function start() {
        $config = \x\Config::get('elasticsearch');
        $node = $config['node'];

        unset($config['node']);
        self::$config = $config;
        
        foreach ($node as $v) {
            self::$normal_node[] = new Connection($v);
        }
    }
    
    /**
     * 正常嗅探
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
    */
    public static function normal_sniff_interval() {
        $time = \x\Config::get('elasticsearch.normal_sniff_interval_time');
        \Swoole\Timer::tick(($time*1000), function (){
            foreach (self::$normal_node as $k=>$node) {
                $res = Request::handle('/', self::GET, json_encode([]), $node);
                $Response = Response::handle($res);
                if ($Response == false) {
                    self::$fault_node[] = $node;
                    unset(self::$normal_node[$k]);
                }
            }
        });
    }
    
    /**
     * 异常嗅探
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
    */
    public static function fault_sniff_interval() {
        $time = \x\Config::get('elasticsearch.fault_sniff_interval_time');
        \Swoole\Timer::tick(($time*1000), function (){
            foreach (self::$fault_node as $k=>$node) {
                $res = Request::handle('/', self::GET, json_encode([]), $node);
                $Response = Response::handle($res);
                if ($Response != false) {
                    self::$normal_node[] = $node;
                    unset(self::$fault_node[$k]);
                }
            }
        });
    }
    
    /**
     * 获得一个正常节点信息
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $exclude 需要排除的节点信息
     * @return array|false
    */
    public static function get_node($exclude=[]) {
        $list = [];
        foreach (self::$normal_node as $k => $v) {
            if (in_array($v->getHost(), $exclude) == false) {
                $list[] = $v;
            }
        }
        if (empty($list)) return false;
        $max = count($list)-1;
        if ($max == 0) return $list[0];
        $key = random_int(0, $max);
        return $list[$key];
    }
}
