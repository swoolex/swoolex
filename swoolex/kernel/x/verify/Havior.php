<?php
/**
 * +----------------------------------------------------------------------
 * 行为验证码
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\verify;
use x\Session;

class Havior {
	/**
	 * 默认存储标识
	*/
	private static $session_key = 'swx_havior';
	/**
	 * APPKEY存储标识
	*/
	private static $session_appkey = '_appkey';
	/**
	 * 状态对应表
	*/
	private static $_status = [
		1 => '请先点击校验',
		2 => '校验失败',
		3 => '校验通过',
		4 => '暴力攻击封禁',
		5 => '验证已过期',
	];

	/**
	 * 生成验证码
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $key 存储标识
	 * @return html
	*/
	public static function create($key = null) {
		$session_key = $key ? $key : self::$session_key;
		Session::set($session_key, 1);
		Session::set($session_key.self::$session_appkey, null);
		return '<div class="swoolex_geetest_captcha" id="'.$session_key.'"></div>';
	}
	
	/**
	 * 校验验证码
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $key 存储标识
	 * @return array
	*/
	public static function check($key = null) {
		$session_key = $key ? $key : self::$session_key;
		$status = Session::get($session_key);
		
		if ($status == 3) {
			Session::set($session_key, 1);
			Session::set($session_key.self::$session_appkey, null);
			return true;
		}

		return self::$_status[$status];
	}

	/**
	 * 行为解析
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param array $param 行为参数
	 * @param string $key 存储标识
	 * @return bool
	*/
	public static function analysis($param, $key = null) {
		$session_key = $key ? $key : self::$session_key;
		if (
			empty($param['swx_appkey']) || 
			empty($param['swx_sign']) || 
			empty($param['swx_time']) || 
			empty($param['swx_geetest']) || 
			empty($param['swx_yes'])
		) {
			return false;
		}
		if (empty(\x\Request::ip())) {
			return self::_return($session_key, 2);
		}
		if (($param['swx_time']-$param['swx_sign']) < 1) {
			return self::_return($session_key, 2);
		}
		if ($param['swx_yes'] <= 0 || $param['swx_yes'] > 6) {
			return self::_return($session_key, 2);
		}
		$s = substr($param['swx_appkey'], $param['swx_yes'], 5+$param['swx_yes']);
		$geetest = rtrim($param['swx_geetest'], $s);
		for ($i=0; $i<$param['swx_yes']; $i++) {
			$geetest = self::decode($geetest);
		}
		$arr = ['&', '|', '=', '!', '%', '*', '#', '^'];
		$vif = false;
		foreach ($arr as $v) {
			if (stripos($geetest, $v) !== false) {
				$vif = $v;
				break;
			}
		}
		if (!$vif) {
			return self::_return($session_key, 2);
		}
		$arr = explode($vif, $geetest);$num=count($arr);
		if ($num != 12 && $num != 14) {
			return self::_return($session_key, 2);
		}
		if (
			$arr[0] != 'a1' || 
			$arr[2] != 'a2' || 
			$arr[4] != 'a3' || 
			$arr[6] != 'a4' || 
			$arr[8] != 'a5' || 
			$arr[10] != 'a6' || 
			(isset($arr[12]) && $arr[12] != 'a7')
		) {
			return self::_return($session_key, 2);
		}
		if (
			is_string($arr[1]) == false || 
			is_numeric($arr[3]) == false || 
			is_numeric($arr[5]) == false || 
			is_numeric($arr[7]) == false || 
			is_numeric($arr[9]) == false || 
			is_numeric($arr[11]) == false || 
			(isset($arr[13]) && is_numeric($arr[13]) == false)
		) {
			return self::_return($session_key, 2);
		}
		if ($arr[11] != 0 && $arr[11] != 1) {
			return self::_return($session_key, 2);
		}
		if ($arr[11] == 0 && $arr[7] < 5) {
			return self::_return($session_key, 2);
		} else if ($arr[11] == 1 && $arr[7] <= 0) {
			return self::_return($session_key, 2);
		}
		if ($arr[9] == 0) {
			return self::_return($session_key, 2);
		}
		if (
			$param['swx_appkey'] != $arr[1] || 
			$param['swx_sign'] != $arr[3] || 
			$param['swx_time'] != $arr[5]
		) {
			return self::_return($session_key, 2);
		}
		if ((time()-7200) > $arr[3]) {
			return self::_return($session_key, 2);
		}
		if (($arr['5']-$arr['3']) > 7200) {
			return self::_return($session_key, 2);
		}
		$appkey = Session::get($session_key.self::$session_appkey);
		if (empty($appkey)) {
			return self::_return($session_key, 5);
		}
		if (
			$appkey['time'] != $arr[3] || 
			$appkey['app_key'] != $arr[1]
		) {
			return self::_return($session_key, 2);
		}
		if (strtoupper(md5(sha1($arr[3]))) != $appkey['app_key']) {
			return self::_return($session_key, 2);
		}
		$top_time = Session::get($session_key.'_time');
		if ($top_time && (time()-$top_time) <= 0) {
			return self::_return($session_key, 2);
		}
		Session::get($session_key.'_time', time());
		
		$head = \x\Request::header();
		$list = [
			'host',
			'user-agent',
			'accept',
			'accept-language',
			'accept-encoding',
			'content-type',
			'x-requested-with',
			'content-length',
			'origin',
			'connection',
			'referer',
		];
		
		foreach ($list as $key) {
			if (empty($head[$key])) {
				return self::_return($session_key, 2);
			}
		}
		
		Session::set($session_key.self::$session_appkey, null);
		return self::_return($session_key, 3);
	}

	/**
	 * 生成APPKEY
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $key 存储标识
	 * @return void
	*/
	public static function appkey($key = null) {
		$session_key = $key ? $key : self::$session_key;
		$time = time();
		$arr = [
			'time' => $time,
			'app_key' => strtoupper(md5(sha1($time))),
		];
		Session::set($session_key.self::$session_appkey, $arr);

		return $arr;
	}

	/**
	 * 封装行为返回值
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $session_key 存储标识
	 * @param int $status 状态
	 * @param int $outtime 失效时间
	 * @return array
	*/
	private static function _return($session_key, $status, $outtime=1800) {
		Session::set($session_key, $status, $outtime);
		$ret = [
			'status' => false,
			'msg' => self::$_status[$status]
		];
		if ($status == 3) {
			$ret['status'] = true;
		}
		return $ret;
	}
	/**
	 * 解密签名
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $str
	 * @return void
	*/
	private static function decode($str){
		$staticchars = "PXhw7UT1B0a9kQDKZsjIASmOezxYG4CHo5Jyfg2b8FLpEvRr3WtVnlqMidu6cN";
		$decodechars = "";
		for($i=1;$i<strlen($str);){
			$num0 = strpos($staticchars, $str[$i]);
			if($num0 !== false){
				$num1 = ($num0+59)%62;
				$code = $staticchars[$num1];
			}else{
				$code = $str[$i];
			}
			$decodechars .= $code;
			$i+=3;
		}
		return $decodechars;
	}
}