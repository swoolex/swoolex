<?php
/**
 * +----------------------------------------------------------------------
 * 图片常规处理
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common;

class Img {

	/**
	 * 图片压缩
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.21 + 2022-02-18
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $src 原图地址
	 * @param string $src 保存地址
	 * @param float $percent 等比比例
	 * @param float $save_width 手改宽度
	 * @param float $save_height 手改高度
	 * @return void
	*/
	public static function compress($src, $save_file=false, $percent=1, $save_width=false, $save_height=false) {
		if ($save_file == false) $save_file = $src;

		list($width, $height, $type, $attr) = getimagesize($src);
        $imageInfo = array(
            'width' => $width,
            'height' => $height,
            'type' => image_type_to_extension($type, false),
            'attr' => $attr
        );
        $fun = "imagecreatefrom" . $imageInfo['type'];
		$image = $fun($src);
		
		$new_width = $save_width ? $save_width : ($imageInfo['width'] * $percent);
        $new_height = $save_height ? $save_height : ($imageInfo['height'] * $percent);
        $image_thump = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($image_thump, $image, 0, 0, 0, 0, $new_width, $new_height, $imageInfo['width'], $imageInfo['height']);
        imagedestroy($image);
		
		$funcs = "image".$imageInfo['type'];
		$funcs($image_thump, $save_file);
		return $save_file;
	}

	/**
	 * 图片转base64字符串
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.21 + 2022.2.18
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $file 图片绝对路径
	 * @return string
	*/
	public static function img_to_base64($file) {
		$img_data = file_get_contents($file);
		$type = getimagesize($file);
		return 'data:'.$type['mime']. ';base64,' . chunk_split(base64_encode($img_data));
	}

	/**
	 * base64字符串转图片保存
	 * @todo 无
	 * @author 小黄牛
	 * @version v2.5.21 + 2022.2.18
	 * @deprecated 暂不启用
	 * @global 无
	 * @param string $base64 base64字符串
	 * @param string $file 保存文件绝对路径
	 * @return bool 
	*/
	public static function base64_to_img($base64, $file) {
		$start = strpos($base64, '/')+1;
		$end = strpos($base64, ';');
		$type = substr($base64, $start, ($end-$start));

		$img = str_replace('data:image/'.$type.';base64,', '', $base64);
		$img = str_replace(' ', '+', $img);
		$data = base64_decode($img);

		return file_put_contents($file, $data);
	}
}
