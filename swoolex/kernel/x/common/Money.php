<?php
/**
 * +----------------------------------------------------------------------
 * 金额相关常用操作工具类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common;

use x\common\money\Simplified;
use x\common\money\Traditional;
use x\common\money\RedPacket;

class Money
{
    /**
     * 大写实例
    */
    private static $NumberObj;
    /**
     * 金额实例
    */
    private static $MoneyObj;
    /**
     * 红包实例
    */
    private static $RedObj;

    /**
     * 随机红包金额分割
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param string $money 总金额
     * @param string $num 红包个数
     * @param string $minMoney 最小金额
     * @return void
    */
    public static function redPacket($money, $num = 1, $minMoney=0.01) {
        self::$RedObj = self::$RedObj ? self::$RedObj : new RedPacket();
        return self::$RedObj->handle($money, $num, $minMoney);
    }

    /**
     * 金额转中文[简体]
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param string $money 金额
     * @return string
    */
    public static function toSimplified($money) {
        self::$NumberObj = self::$NumberObj ? self::$NumberObj : new Simplified();
        return self::$NumberObj->toChinese($money);
    }

    /**
     * 金额转中文[繁体]
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param string $money 金额
     * @return string
    */
    public static function toTraditional($money) {
        self::$MoneyObj = self::$MoneyObj ? self::$MoneyObj : new Traditional();
        return self::$MoneyObj->toChinese($money);
    }

    /**
     * 中文转金额[兼容]
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param string $str 中文金额
     * @return float
    */
    public static function toChinese($str) {
        $arr = ['零','壹','贰','叁','肆','伍','陆','柒', '捌','玖','拾','佰','仟'];
        $key = mb_substr($str, 0, 1);
        if ($key == '负') {
            $key = mb_substr($str, 1, 1);
        }
        if (in_array($key, $arr)) {
            self::$MoneyObj = self::$MoneyObj ? self::$MoneyObj : new Traditional();
            $money = self::$MoneyObj->toNumber($str);
        } else {
            self::$NumberObj = self::$NumberObj ? self::$NumberObj : new Simplified();
            $money = self::$NumberObj->toNumber($str);
        }

        if (strpos($money, '.') === false) {
            return (int)$money;
        }
        return (float)$money;
    }

    /**
     * 金额千分符格式化
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $money
     * @return string
    */
    public static function format($money) {
        return number_format($money, 2, '.', ',');
    }

    /**
     * 验证是否正确数字
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $money
     * @return bool
    */
    public static function verify($money) {
        return preg_match('/^-?\d+(\.\d+)?$/', $money) > 0;
    }

    /**
     * 元转分
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $money
     * @return float
    */
    public static function toCent($money, $scale=2) {
        return (float)bcmul($money, 100, $scale);
    }

    /**
     * 分转元
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $money
     * @return float
    */
    public static function toDollar($money, $scale=2) {
        return (float)bcdiv($money, 100, $scale);
    }

    /**
     * 两个金额是否一致
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $rightMoney
     * @param float $rightMoney
     * @return float
    */
    public static function equal($leftMoney, $rightMoney) {
        return (bccomp($leftMoney, $rightMoney) == 0 ? true : false);
    }

    /**
     * 保留小数
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param float $money 金额
     * @param int $bit 小数位
     * @param float $status 是否四舍五入
     * @return float
    */
    public static function round($money, $bit=2, $status=false) {
        if (strpos($money, '.') === false) return $money;
        if ($bit == 0 ) {
            if ($status) {
                return (int)ceil($money);
            } else {
                $arr = explode('.', $money);
                return (int)$arr[0];
            }
        }
        if ($status) {
            return round($money, $bit);
        } else {
            $num = 1;
            for ($i=0; $i<$bit; $i++) {
                $num .= 0;
            }
            return (float)bcdiv(bcmul($money, $num, $bit), $num, $bit);
        }
    }
}

