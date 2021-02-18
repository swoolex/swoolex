<?php
// +----------------------------------------------------------------------
// | 验证码类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x;

class Verify {
	/**
	 * 英数混合模式下，使用的字符串，01IO容易混淆已经删除
	 */
	public static $codeSet = '2346789ABCDEFGHJKLMNPQRTUVWXY';
	/**
	 * 验证码常规配置信息
	 */
	public static $_TYPE = [];
	/**
	 * 验证码的背景颜色
	 */
	private static $_RGB = [243, 251, 254];
	/**
	 * 验证码使用的文字路径
	 */
	private static $_URL;
	/**
	 * 验证码图片实例
	 */
	private static $_IMAGE = null;
	/**
	 * 验证码字体颜色
	 */
	private static $_COLOR = null;
	/**
	 * 验证码SESSION名称
	 */
	private static $_SESSION_NAME = '__vif__';
	
	/**
	 * 输出验证码;
	 * @param int $num 验证码使用模式 默认为英数混合 1英数混合 2数字运算
	 * @param string $session 验证码的seesion名
	 * @param array $type 验证码属性
	 * @param swool\response
	 * @return bool
	*/
	public static function entry($num=1, $session=null, $type=null, $response) {
		self::construct($type);
		# 设置验证码字体路径
		self::$_URL = ROOT_PATH . '/swoolex/ttf/' .self::$_TYPE['ttf'];
		# 设置验证码SESSION名
		if (!empty($session)) {
			self::$_SESSION_NAME = $session;
		}

		# 生成画布实例
		self::$_IMAGE = imagecreate(self::$_TYPE['width'], self::$_TYPE['height']);
		# 画布背景颜色						 
		imagecolorallocate(self::$_IMAGE, self::$_RGB[0], self::$_RGB[1], self::$_RGB[2]);			
		# 设置画布文字颜色
		self::$_COLOR = imagecolorallocate(self::$_IMAGE, mt_rand(1,120), mt_rand(1,120), mt_rand(1,120));
		# 生成绘杂点
		if (self::$_TYPE['curve']) {
			self::_writeNoise();
		} 
		# 生成绘干扰线
		if (self::$_TYPE['noise']) {
			self::_writeCurve();
		}
		# 判断验证码模式
		switch ($num) {
		case 1:
			$code = self::nbcode(); // 英数
			self::_SESSION($code);
			break;  
		case 2:
			$code = self::aocode(); // 运算
			self::_SESSION($code);
			break;
		}
		# 响应到页面头部
		$response->header('Cache-Control', 'private, max-age=0, no-store, no-cache, must-revalidate');
		$response->header('Pragma', 'no-cache');		
		$response->header('content-type', 'image/png');
		# 输出图像
		ob_start();
		imagepng(self::$_IMAGE); 
		$content = ob_get_clean();
    	$response->end($content);
		imagedestroy(self::$_IMAGE);
	}

	/**
	 * 初始化常规配置 
	 * @param array $type 验证码属性
	*/
	private static function construct($type=null) {
		# 先合并配置文件中的常规配置
		self::$_TYPE = array_merge(self::$_TYPE, \x\Config::get('app.verify'));
		# 设置验证码常规配置
		if (is_array($type)) {
			self::$_TYPE = array_merge(self::$_TYPE, $type);
		}
	}

	/** 
	 * 画一条由两条连在一起构成的随机正弦函数曲线作干扰线(算法转载与网络) 
     *      
	 *	正弦型函数解析式：y=Asin(ωx+φ)+b
	 *  各常数值对函数图像的影响：
	 *  A：决定峰值（即纵向拉伸压缩的倍数）
	 *  b：表示波形在Y轴的位置关系或纵向移动距离（上加下减）
	 *  φ：决定波形与X轴位置关系或横向移动距离（左加右减）
	 *  ω：决定周期（最小正周期T=2π/∣ω∣）
	 *
	*/
	protected static function _writeCurve() {
		$height = self::$_TYPE['height'];
		$width = self::$_TYPE['width'];
		$A = mt_rand(1, $height/2);            // 振幅
		$b = mt_rand(-$height/4, $height/4);   // Y轴方向偏移量
		$f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量
		$T = mt_rand($height*1.5, $width*2);  // 周期
		$w = (2* pi())/$T;
						
		$px1 = 0;  // 曲线横坐标起始位置
		$px2 = mt_rand($width/2, $width * 0.667);  // 曲线横坐标结束位置 	    	
		for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
			if ($w!=0) {
				$py = $A * sin($w*$px + $f)+ $b + $height/2;
				$i = (int) ((self::$_TYPE['fontsize'] - 6)/4);
				while ($i > 0) {	
				    imagesetpixel(self::$_IMAGE, $px + $i, $py + $i, self::$_COLOR);				
				    $i--;
				}
			}
		}
		
		$A = mt_rand(1, $height/2);            // 振幅		
		$f = mt_rand(-$height/4, $height/4);   // X轴方向偏移量
		$T = mt_rand($height*1.5, $width*2);  // 周期
		$w = (2* M_PI)/$T;		
		$b = $py - $A * sin($w*$px + $f) - $height/2;
		$px1 = $px2;
		$px2 = $width;
		for ($px=$px1; $px<=$px2; $px=$px+ 0.9) {
			if ($w!=0) {
				$py = $A * sin($w*$px + $f)+ $b + $height/2;
				$i = (int) ((self::$_TYPE['fontsize'] - 8)/4);
				while ($i > 0) {			
				    imagesetpixel(self::$_IMAGE, $px + $i, $py + $i, self::$_COLOR); 	
				    $i--;
				}
			}
		}
	}

	/**
	 * 画杂点
	 * 往图片上写不同颜色的字母或数字
	*/
	protected static function _writeNoise() {
		for($i = 0; $i < 10; $i++){
			# 杂点颜色
		    $noiseColor = imagecolorallocate(
		                      self::$_IMAGE, 
		                      mt_rand(150,225), 
		                      mt_rand(150,225), 
		                      mt_rand(150,225)
		                  );
			for($j = 0; $j < 10; $j++) {
				# 绘杂点
			    imagestring(
			        self::$_IMAGE,
			        mt_rand(1,5), 
			        mt_rand(-10, self::$_TYPE['width']), 
			        mt_rand(-10, self::$_TYPE['width']), 
					# 杂点文本为随机的字母或数字
			        self::$codeSet[mt_rand(0, 28)],
			        $noiseColor
			    );
			}
		}
	}

	/**
	 * 英数混合验证码
	 * @return string 验证码内容
	*/
	protected static function nbcode() {
		# 绘验证码
		$code = [];
		# 验证码第N个字符的左边距
		$codeNX = 0; 
		for ($i = 0; $i<self::$_TYPE['length']; $i++) {
			$code[$i] = strtolower(self::$codeSet[mt_rand(0, 28)]);
			$codeNX += mt_rand(self::$_TYPE['fontsize']*1.2, self::$_TYPE['fontsize']*1.8);
			# 写入一个验证码字符
			imagettftext(
				self::$_IMAGE, 
				self::$_TYPE['fontsize'], 
				mt_rand(-20, 50), 
				$codeNX, 
				self::$_TYPE['fontsize']*1.5, 
				self::$_COLOR, 
				self::$_URL, 
				$code[$i]
			);
		}
		return $code;
	}

	/**
	 * 运算型验证码
	 * @return string 验证码内容
	*/
	protected static function aocode() {
		# 绘验证码
		$code = 0;
		$left = self::$_TYPE['width']/10; 
		#第一个数字
		$shu = rand(1,9);  
		# 写入第一个数字参数
		imagettftext(self::$_IMAGE, self::$_TYPE['fontsize'], mt_rand(0, 20), $left, self::$_TYPE['fontsize']*1.5, self::$_COLOR, self::$_URL, $shu);
		# 左间距的值变化,保证文字不会叠加到一起
		$left += self::$_TYPE['width']/5;
		# 写入第二个数字参数
		$shu2 = rand(1,9);
		imagettftext(self::$_IMAGE, self::$_TYPE['fontsize'], mt_rand(0, 20), $left*2, self::$_TYPE['fontsize']*1.5, self::$_COLOR, self::$_URL, $shu2);
		# 左间距的值变化,保证符合会出现在数字中间
		$left += self::$_TYPE['width']/14; 
		# 中间的运算符
		$num = rand(1,3);                                                                                             	
		switch ($num) {
		case 1:
		  $count='x';         // 定义中间的运算符
		  $code = $shu*$shu2; // 得到运算的结果
		  break;  
		case 2:
		  $count='+';
		  $code = $shu+$shu2;
		  break;
		case 3:
		  $count='-';
		  $code = $shu-$shu2;
		  break;
		}
		# 写入中间运算符参数
		imagettftext(self::$_IMAGE, self::$_TYPE['fontsize'], mt_rand(0, 20), $left, self::$_TYPE['fontsize']*1.5, self::$_COLOR, self::$_URL, $count); 
		# 左间距的值变化,保证等号会出现在最右边
		$left += self::$_TYPE['width']/2.5;
		# 写入最后的等号
		imagettftext(self::$_IMAGE, self::$_TYPE['fontsize']*1.3, mt_rand(0, 30), $left*1.1, self::$_TYPE['fontsize']*1.5, self::$_COLOR, self::$_URL, '=');
		return $code;
	}

	/**
	 * 验证码保存
	*/
	protected static function _SESSION($code) {
		# 如果是数组则转字符串
		if (is_array($code)) {
			$code = implode('', $code);
		}
		# 设置验证码
		\x\Session::set(self::$_SESSION_NAME, $code, self::$_TYPE['expire']);
	}

	/**
	 * 核验验证码
	 * @param string $code 用户验证码
	 * @param string $session 验证码保存的seesion名
	 * @param boool
	*/
	public static function check($code, $session=null) {
		self::construct();
		if (empty($session)) {
			$session = self::$_SESSION_NAME;
		}
		$string = \x\Session::get($session);
		# 是否需要更新验证码
		if (self::$_TYPE['update']) {
			\x\Session::delete($session);
		}
		# 验证码不能为空
		if(empty($code) || empty($string)) {
			return false;
		}
		# 验证码正确
		if (strtolower($code) == $string) {
			return true;
		}
		return false;
	}
}