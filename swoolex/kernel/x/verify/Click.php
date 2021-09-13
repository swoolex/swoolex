<?php
/**
 * +----------------------------------------------------------------------
 * 点图验证码
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

class Click {
	/**
	 * 默认存储标识
	*/
	private static $session_key = 'swx_click';
	/**
	 * 状态对应表
	*/
	private static $_status = [
		1 => '点击进行人机身份验证',
		2 => '验证码已失效',
		3 => '校验通过',
		4 => '校验失败',
	];

	/**
	 * 生成验证码
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $name 表单name值
	 * @param bool $status 校验失败后，是否清空session
	 * @param string $key 存储标识
	 * @return html
	*/
	public static function create($name='sw_click_name', $status=true, $key = null) {
		$session_key = $key ? $key : self::$session_key;
		return '<div class="swoolex_google_captcha" id="'.$session_key.'" data-name="'.$name.'" data-status="'.($status ? 1 : 0).'"></div>';
	}
	
	/**
	 * 校验验证码
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $key 存储标识
	 * @return bool
	*/
	public static function check($key = null) {
		$session_key = $key ? $key : self::$session_key;
		$status = Session::get($session_key);

		if ($status == 3) {
			Session::set($session_key, 1);
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
	 * @param string $name 表单name值
	 * @param bool $status 校验失败后，是否清空session
	 * @param string $key 存储标识
	 * @return array
	*/
	public static function analysis($name="sw_click_name", $status=true, $key = null) {
		$param = \x\Request::param();
		if (empty($param[$name])) return self::_return($session_key, $status, 1);
		$code = $param[$name];
		$session_key = $key ? $key : self::$session_key;
		$res = Session::get($session_key);
		if ($res == 1) return self::_return($session_key, $status, 1);
		if (!$res) return self::_return($session_key, $status, 2);
		$yes_arr = explode(',', $res);
        $vif_arr = explode(',', trim($code, ','));
		// 先验证个数是否一致
        if (count($vif_arr) != count($yes_arr)) {
            return self::_return($session_key, $status, 4);
		}
		// 再验证id是否一致
        foreach ($vif_arr as $v) {
            if (!in_array($v, $yes_arr)) {
				return self::_return($session_key, $status, 4);
            }
		}
		return self::_return($session_key, $status, 3);
	}
	
	/**
	 * 生成验证图床
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param array $img_list 图库列表
	 * @param string $key 存储标识
	 * @return array
	*/
	public static function create_img($img_list, $key = null) {
		if (empty($img_list)) return false;
		$length = count($img_list);
		if ($length <= 1) return false;
		$session_key = $key ? $key : self::$session_key;
		Session::set($session_key, 1);
		// 正确图片数量
		$num   = 3;
		// 随机出一个正确分类
		$rand = mt_rand(1, $length);
		$check_title = '';
		$check_list = [];
		$i = 0;
		foreach ($img_list as $key => $value) {
			$i++;
			if ($i == $rand) {
				$check_title = $key;
				$check_list = $value;
				break;
			}
		}
		//  2、根据随机分类，获得指定数量的图片
		$yes_list  = [];
		$count = count($check_list)-1;
		for ($i=1; $i<=$num; $i++) {
			$k = mt_rand(1, $count);
			$suffix = substr(strrchr($check_list[$k], '.'), 1);
			$result = basename($check_list[$k],".".$suffix);
			$yes_list[] = [
				'id' => $result,
				'url' => $check_list[$k],
			];
		}
		// 3、再随机，获得除了指定分类外的其他图片
		$disturb_list = [];
		unset($img_list[$check_title]);
		$length--;
		asort($img_list);

		for ($z=1; $z<7; $z++) {
			$i = 0;
			$rand = mt_rand(1, $length);
			foreach ($img_list as $k=>$v) {
				$i++;
				if ($i==$rand) {
					$num = count($v)-1;
					$r = mt_rand(0, $num);
					$suffix = substr(strrchr($v[$r], '.'), 1);
					$result = basename($v[$r],".".$suffix);
					$disturb_list[] = [
						'id' => $result,
						'url' => $v[$r],
					];
					break;
				}
			}
		}
		// 4、将两组图片随机打乱合并成一组新数据
		// 先for，获得图片id，并写入session
        $vif_id = '';
        foreach ($yes_list as $val) {
            $vif_id .= $val['id'].',';
        }
        $vif_id = rtrim($vif_id, ',');
        $res = Session::set($session_key, $vif_id);
        if (!$res) {
            return false;
		}
		// 随机合并并返回
		$list   = self::ShuffleAssoc(array_merge_recursive($yes_list, $disturb_list));
		return [
			'title' => $check_title,
			'list' => $list,
		];
	}

	/**
	 * 二维数组随机排序
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
     * @param array  $list 需要排序的数组
	 * @return array
	*/
    private static function ShuffleAssoc($list) {  
        if (!is_array($list)) return $list;  
           
        $keys = array_keys($list);  
        shuffle($keys);  
        $random = array();  
        foreach ($keys as $key) {
            $random[] = $list[$key];  
        }
        return $random;  
	}
	/**
	 * 返回状态信息
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.6 + 2021-09-11
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $session_key 存储标识
	 * @param bool $status 校验失败后，是否清空session
	 * @param int $code 状态码
	 * @return void
	*/ 
	private static function _return($session_key, $status, $code) {
		Session::set($session_key, $code);
		$ret = [
			'status' => false,
			'msg' => self::$_status[$code]
		];
		if ($code == 3) {
			$ret['status'] = true;
		}
		return $ret;
	}
}