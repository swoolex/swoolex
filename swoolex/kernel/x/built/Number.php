<?php
/**
 * +----------------------------------------------------------------------
 * 订单编号生成
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Number
{
    /**
     * 使用redis生成订单号[唯一]
     * @todo 依赖Redis连接池
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 尾号长度
     * @param int $type 方式 1.按年 2.按月 3.按天 4.按小时
     * @param int $prefix Redis的前缀
     * @return int
    */
    public static function redisSn($length=7, $type=3, $prefix="sn") {
        switch ($type) {
            case 1: 
                if ($length > 15) return false;
                $key = date('Y', time()); $outime = (86400*365); 
            break;
            case 2: 
                if ($length > 13) return false;
                $key = date('Ym', time()); $outime = (86400*31); 
            break;
            case 3: 
                if ($length > 11) return false;
                $key = date('Ymd', time()); $outime = 86400; 
            break;
            case 4: 
                if ($length > 9) return false;
                $key = date('Ymd H', time()); $outime = 3600;
            break;
            default:
                return false;
            break;
        }
        $prefix .= $key.$length;
        
        
        $Redis = new \x\Redis();
        if ($Redis->get($prefix) == false) {
            $res = $Redis->SETEX($prefix, $outime, 0);
            if ($res == false) {
                $Redis->return();
                return false;
            }
        }
        $res = $Redis->INCR($prefix, 1);
        if ($res == false) {
            $Redis->return();
            return false;
        }
        $ret = $Redis->get($prefix);
        $Redis->return();
        if ($ret == false) {
            return false;
        }
        $max = $length-strlen($ret);
        $num = '';
        for ($i=0; $i<$max; $i++) {
            $num .= 0;
        }
    
        return $key.$num.$ret;
    }

    /**
     * 生成随机验证码[有可能重复]
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @param int $type 方式 1.按年 2.按月 3.按天 4.按小时
     * @return string 
    */
    public static function strSn($length=7, $type=3) {
        switch ($type) {
            case 1: $ret = date('Y', time()); break;
            case 2: $ret = date('Ym', time()); break;
            case 3: $ret = date('Ymd', time()); break;
            case 4: $ret = date('Ymd H', time()); break;
            default:
                return false;
            break;
        }

        return $ret.self::random($length);
    }

    /**
     * 生成任意N位数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @return string 
    */
    private static function random($length) {
        $seed = base_convert(md5(microtime() . mt_rand(100000, 999999)), 16, 10);
        $seed = str_replace('0', '', $seed) . '012340567890';
        $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
        $length--;
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return strtoupper($hash);
    }
}