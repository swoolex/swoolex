<?php
/**
 * +----------------------------------------------------------------------
 * 文字识别
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Ocr 
{
    /**
     * 地址详情中识别出省市区名称
     * @author 小黄牛
     * @version v2.5.8 + 2021-10-22
     * @param string $address 地址详情
     * @param bool $status 是否删除省市区后缀
     * @return array
    */
    public static function region($address, $status=false) {
        $array = ['北京', '天津', '上海', '重庆'];
        preg_match('/(.*?(省|自治区))/', $address, $matches);
        if (count($matches) > 1) {
            $province = $matches[count($matches) - 2];
            $address = preg_replace('/(.*?(省|自治区))/','', $address, 1);
        } else {
            $str = mb_substr($address, 0, 2);
            if (in_array($str, $array)) {
                $province = $str;
                $three = mb_substr($address, 2, 1);
                if ($three == '市') {
                    $address = mb_substr($address, 3, mb_strlen($address));
                } else {
                    $address = mb_substr($address, 2, mb_strlen($address));
                }
            }
        }
        preg_match('/(.*?(市|自治州|地区|区划|县))/', $address, $matches);
        if (count($matches) > 1) {
            $city = $matches[count($matches) - 2];
            $address = str_replace($city, '', $address);
        }
        if (!isset($city) && in_array($province, $array)) {
            $str = mb_substr($address, 0, 2);
            echo $city;
            if (in_array($str, $array)) {
                $city = $str;
                $three = mb_substr($address, 2, 1);
                if ($three == '市') {
                    $address = mb_substr($address, 3, mb_strlen($address));
                } else {
                    $address = mb_substr($address, 2, mb_strlen($address));
                }
            }
        }
        preg_match('/(.*?(区|县|镇|乡|街道))/', $address, $matches);
        if (count($matches) > 1) {
            $area = $matches[count($matches) - 2];
            $address = str_replace($area, '', $address);
        }

        $ret = [
            'province' => isset($province) ? $province : '',
            'city' => isset($city) ? $city : '',
            'area' => isset($area) ? $area : '',
            "address" => $address
        ];

        if ($status == true && $ret['province']) {
            $ret['province'] = str_replace(['省', '自治区'], '', $ret['province']);
        }
        if ($status == true && $ret['city']) {
            $ret['city'] = str_replace(['市', '自治州', '地区', '区划', '县'], '', $ret['city']);
        }
        if ($status == true && $ret['area']) {
            $array = ['区','县','镇','乡','街道'];
            foreach ($array as $v) {
                $str = substr($ret['area'],0,strrpos($ret['area'], $v));
                if (!$str) break;
                $ret['area'] = $str;
            }
        }

        return $ret;
    }

    /**
     * 收件地址文字识别
     * @author 小黄牛
     * @version v2.5.8 + 2021-10-22
     * @param string $str 需要识别的文字
     * @return array
    */
    public static function address($str=''){
        $str = str_replace(['，',',','#','￥','$','^','；', '。', '%', '&', ';', '；', '~', '·', '[', ']', '【', '】', '"', '”', '“', '/', '？', '?', '《','》', '<', '>', '!', '！'],' ', $str);
        $str = strip_tags($str);
        $str = str_replace(["\r\n", "\r", "\n"], '', $str);
        $str = preg_replace("/\s(?=\s)/","\\1", $str);
        $str = trim($str);
        $str = str_replace(' ', ',', $str);

        $name = '';
        $phone = '';
        $address = '';
        $email = '';
        $sex = '';
        $list = [];

        $arr = explode(',', $str);

        if (count($arr) == 1) {
            $str = $arr[0];
            preg_match_all("/([0-9]+)+/i", $str, $array);
            if (!empty($array[1][0])) {
                foreach ($array[1] as $value) {
                    if (preg_match("/^1[23456789]\d{9}$/", $value)) {
                        $phone = $value;
                        $str = str_replace($phone, '', $str);
                        break;
                    }
                }
            }
            preg_match_all("/([a-z0-9\-_\.]+@[a-z0-9]+\.[a-z0-9\-_\.]+)+/i", $str, $array);
            if (!empty($array[1][0])) {
                $email = $array[1][0];
                $str = str_replace($email, '', $str);
            }
            $address = $str;
        } else {
            $s = '';
            foreach ($arr as $k=>$v) {
                if (is_numeric($v) && strlen($v) == 11 && preg_match("/^1[23456789]\d{9}$/", $v)) {
                    $s = $v;
                    $skey = $k;
                }
            }
            if ($s) {
                $phone = $s;
                unset($arr[$skey]);
            }
            $regex= '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
            foreach ($arr as $k=>$v) {
                if (preg_match($regex, $v)) {
                    $email = $v;
                    unset($arr[$k]);
                    break;
                }
            }
            foreach ($arr as $k=>$v) {
                if (mb_strlen($v) <= 2) {
                    if (in_array($v, ['未知', '男', '女', '先生', '小姐', '美女', '帅哥', '帥哥'])) {
                        if (in_array($v, ['男', '先生', '帅哥', '帥哥'])) {
                            $sex = '男';
                        } else if (in_array($v, ['女', '小姐', '美女'])) {
                            $sex = '女';
                        } else {
                            $sex = $v;
                        }
                        unset($arr[$k]);
                        break;
                    }
                }
            }
            $s = '';
            $skey = '';
            $i = 0;
            foreach ($arr as $k=>$v) {
                $lenth = mb_strlen($v);
                if ($lenth > 1) {
                    if ($i == 0) {
                        $s = $v;
                        $skey = $k;
                    } else {
                        if (mb_strlen($s) > $lenth) {
                            $s = $v;
                            $skey = $k;
                        }
                    }
                    $i++;
                }
            }
            if ($s) {
                $name = $s;
                unset($arr[$skey]);
            }
            $s = '';
            $skey = '';
            $i = 0;
            foreach ($arr as $k=>$v) {
                if ($i == 0) {
                    $s = $v;
                    $skey = $k;
                } else {
                    if (strlen($s) < strlen($v)) {
                        $s = $v;
                        $skey = $k;
                    }
                }
                $i++;
            }
            if ($s) {
                $address = $s;
                unset($arr[$skey]);
            }
            $list = $arr;
            if ($name) {
                if (stripos($name, '先生') !== false || stripos($name, '帅哥') !== false || stripos($name, '帥哥') !== false ) {
                    $sex = '男';
                } else if (stripos($name, '小姐') !== false || stripos($name, '美女') !== false) {
                    $sex = '女';
                } 
            }
        }

        
        $array = [];
        if (!empty($address)) {
            $array = self::region($address);
        }
        $list = array_values($list);
        return [
            'full_name' => $name,
            'sex' => $sex,
            'phone' => $phone,
            'email' => $email,
            'province' => isset($array['province']) ? $array['province'] : '',
            'city' => isset($array['city']) ? $array['city'] : '',
            'area' => isset($array['area']) ? $array['area'] : '',
            'local_address' => isset($array['address']) ? $array['address'] : '',
            'complete_address' => $address,
            'list' => $list,
        ];
    }
}