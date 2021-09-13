<?php
/**
 * +----------------------------------------------------------------------
 * 点图验证码支持类
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

class SwClick extends Http
{

    /**
     * @RequestMapping(route="/SwClick/get_img", method="post", title="获取图片列表")
    */
    public function get_img() {
        $param = \x\Request::post();
        if (empty($param['key'])) return $this->_json('301', '非法请求');
        $img_list = [];
        /* 
            格式：
            [
                '动物' => [
                    '图片地址1',
                    '图片地址2',
                ],
                '箱子' => [
                    '图片地址1',
                    '图片地址2',
                ]
            ]
        */
        $arr = \x\verify\Click::create_img($img_list, $param['key']);
        return $this->_json('200', "请点击下列所有<font color='red'> ".$arr['title']." </font>图片后，点击提交！", $arr['list']);
    }

    /**
     * @RequestMapping(route="/SwClick/check", method="post", title="提交行为校验")
    */
    public function check() {
        $param = \x\Request::post();
        if (empty($param['key'])) return $this->_json('301', '非法请求');
        // 失败将清空session，前端要配合刷新验证码
        $arr = \x\verify\Click::analysis('sw_click_name', true, $param['key']);
        if ($arr['status']) {
            return $this->_json('200', $arr['msg']);
        }
        return $this->_json('301', $arr['msg']);
    }

    /**
     * 返回固定的JSON格式
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-11
     * @deprecated 暂不启用
     * @global 无
     * @param string $code 状态码
     * @param string $msg 描述
     * @param mixed $data 数据集
     * @return json
    */
    private function _json($code, $msg, $data=null) {
        return $this->fetch(json_encode([
            'code' => "{$code}",
            'msg' => $msg,
            'data' => $data,
        ], JSON_UNESCAPED_UNICODE));
    }
}