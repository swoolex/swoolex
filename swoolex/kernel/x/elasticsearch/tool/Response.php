<?php
/**
 * +----------------------------------------------------------------------
 * 响应处理
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\tool;
use Exception;

class Response
{
    /**
     * 处理结果
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $ret 请求结果
     * @return mixed
    */
    public static function handle($ret) {
        if ($ret === false) return false;
        $array = json_decode($ret, true);

        if (isset($array['error'])) return $array['error'];
        
        return $array;
    }
}
