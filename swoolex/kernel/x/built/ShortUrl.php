<?php
/**
 * +----------------------------------------------------------------------
 * 短网址常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class ShortUrl
{
    /**
     * 创建短链接
     * @todo 依赖Redis连接池
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @param string $url 网址
     * @param int $outtime 过期时间[默认31天]
     * @param int $redis_key Redis自增器名称
     * @return string 返回短链接标识符
    */
    public static function set($url, $outtime=2678400, $redis_key='swx_shorturl_inc') {
        $Redis = new \x\Redis();

        $arr = parse_url($url);
        $del = $arr['path'];
        if (isset($arr['query'])) $del = $arr['query'];

        $route = str_replace($del, '', $url);
        $md5 = strtoupper(substr(md5($route), 0, 5));

        $incrby = $Redis->get($redis_key);
        $Redis->incrby($redis_key, 1);

        $ret = $md5.$incrby;
        $Redis->set($ret, $url);
        $Redis->expire($ret, $outtime);
        $Redis->return();

        return $ret;
    }

    /**
     * 获取链接
     * @todo 依赖Redis连接池
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @deprecated 暂不启用
     * @global 无
     * @param string $code 短链接标识符
     * @return string|false 需要跳转的链接地址
    */
    public static function get($code) {
        $Redis = new \x\Redis();
        $url = $Redis->get($code);
        $Redis->return();
        return $url;
    }
}