<?php
// +----------------------------------------------------------------------
// | Csrf验证类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Csrf {
	/**
	 * 生成Token随机令牌字符
	 */
	public $code = 'ABCDEFGHIJKLMNPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz123456789';

	/**
	 * 生成请求令牌
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @deprecated 暂不启用
	 * @global 无
	 * @return string
	*/
    public function create_token() {
        # 生成Token随机令牌
        $length = mb_strlen($this->code, 'utf-8');
        $Token  = '';
        for ($i=1; $i<=32; $i++) {
            $rand   = rand(1, ($length-1));
            $Token .= mb_substr($this->code, $rand, 1, 'utf-8');
        }
		# 更新csrf_token
		$config = \x\Config::get('jwt');
		\x\Session::set($config['csrf_session_name'], $Token, $config['csrf_outtime']);

        return $Token;
	}

	/**
	 * 验证请求令牌
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $token
     * @return bool
	*/
    public function is_token($token) {
		$config = \x\Config::get('jwt');
        $csrf_token = \x\Session::get($config['csrf_session_name']);

        if (empty($csrf_token)) return false;
		if ($csrf_token != $token) return false;

        return true;
	}
	
	/**
	 * 清除请求令牌
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.0.6 + 2021.4.26
	 * @deprecated 暂不启用
	 * @global 无
	 * @return bool
	*/
    public function clean_token() {
		# 更新csrf_token
		$config = \x\Config::get('jwt');
		return \x\Session::delete($config['csrf_session_name']);
    }
}