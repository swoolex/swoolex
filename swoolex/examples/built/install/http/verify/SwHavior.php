<?php
/**
 * +----------------------------------------------------------------------
 * 行为验证码支持类
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

class SwHavior extends Http
{
    /**
     * @RequestMapping(route="/SwHavior/get_appkey", method="post", title="获取并刷新APPKEY")
    */
    public function get_appkey() {
        $param = \x\Request::post();
        if (empty($param['key'])) return $this->_json('301', '非法请求');
        $arr = \x\verify\Havior::appkey($param['key']);
        return $this->_json('200', '成功', $arr);
    }

    /**
     * @RequestMapping(route="/SwHavior/check", method="post", title="提交行为校验")
    */
    public function check() {
        $param = \x\Request::post();
        if (empty($param['key'])) return $this->_json('301', '非法请求');
        $arr = \x\verify\Havior::analysis($param, $param['key']);
        if ($arr['status']) {
            return $this->_json('200', $arr['msg']);
        }
        return $this->_json('301', $arr['msg']);
    }

    // 返回固定的JSON格式
    private function _json($code, $msg, $data=null) {
        return $this->fetch(json_encode([
            'code' => "{$code}",
            'msg' => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE));
    }
}