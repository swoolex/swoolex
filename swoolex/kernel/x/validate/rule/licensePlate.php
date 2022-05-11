<?php
/**
 * +----------------------------------------------------------------------
 * 国内有效的车牌号
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class licensePlate
{
    /**
     * 入口方法
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @param array $data 完整表单
     * @param mixed $param 参数值
     * @param string $rule :后参数
     * @return bool
    */
    public static function run($data, $param, $rule=null) {
        // 民用车牌和使馆车牌
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新使]{1}[A-Z]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        // 特种车牌(挂,警,学,领,港,澳)
        $regular = '/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{4}[挂警学领港澳]{1}$/u';
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        // 武警车牌
        $regular = '/^WJ[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]?[0-9a-zA-Z]{5}$/ui';
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        // 军牌
        $regular = "/[A-Z]{2}[0-9]{5}$/";
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        // 新能源车辆6位车牌
        // 小型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[DF]{1}[0-9a-zA-Z]{5}$/u";
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        // 大型新能源车
        $regular = "/[京津冀晋蒙辽吉黑沪苏浙皖闽赣鲁豫鄂湘粤桂琼川贵云渝藏陕甘青宁新]{1}[A-Z]{1}[0-9a-zA-Z]{5}[DF]{1}$/u";
        preg_match($regular, $param, $match);
        if (isset($match[0])) {
            return true;
        }
        return false;
    }
}