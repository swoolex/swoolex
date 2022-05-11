<?php
/**
 * +----------------------------------------------------------------------
 * 繁体金额转换
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common\money;

class Traditional
{
    /**
     * 数字部分
    */
    public $number = [
        0   => '零',
        1   => '壹',
        2   => '贰',
        3   => '叁',
        4   => '肆',
        5   => '伍',
        6   => '陆',
        7   => '柒',
        8   => '捌',
        9   => '玖',
        '-' => '负',
        '.' => '.',
    ];
    /**
     * 进阶单位部分
    */
    public $unit = [
        '拾',
        '佰',
        '仟',
        '万',
        '亿',
    ];
    /**
     * 负数单位部分
    */
    public $negative = [
        '角',
        '分',
        '厘',
        '毫',
    ];

    /**
     * 金额转繁体
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @param string $money 金额
     * @return string
    */
    public function toChinese($money) {
        if (!\x\common\Money::verify($money)) return false;

        list($integer, $decimal) = explode('.', $money.'.');

        $pom = '';
        // 有负数
        if ($integer < 0) {
            $pom = $this->number['-'];
            // 取反
            $integer = abs($integer);
        }
        $integerPart = $this->parseInteger($integer);
        if ($integerPart === '') {
            $integerPart = $this->number[0];
        }
        $decimalPart = $this->parseDecimal($decimal);
        if (!$decimalPart) {
            $decimalPart = '元整';
        } else {
            $decimalPart = '元'.$decimalPart;
        }
        return $pom . $integerPart . $decimalPart;
    }
    
    /**
     * 简体转金额
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @param string $str 中文金额
     * @return int|float
    */
    public function toNumber($str) {
        $str = str_replace($this->negative, '', $str);
        $str = str_replace('整', '', $str);
        $length = mb_strlen($str);
        $number = $partNumber = 0;
        $pom = 1; 
        $lastNum = 0;
        $isDecimal = false;
        $decimal = '';
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($str, $i, 1);
            if ($i==0 && $this->number['-'] === $char) {
                $pom = -1;
                continue;
            }
            if ($char === '元') {
                $isDecimal = true;
                continue;
            }
            $key = array_search($char, $this->number);
            if ($key===false) {
                $key = array_search($char, $this->unit);
                if ($key == 0 && $lastNum == 0) {
                    $lastNum = 1;
                }

                if ($key >= 3) {
                    $partNumber += $lastNum;
                    $number += $partNumber * bcpow(10, (($key - 3) * 4) + 4);
                    $partNumber = 0;
                } else {
                    $partNumber += $lastNum * bcpow(10, $key + 1);
                }

                $lastNum = 0;
            } else {
                if ($isDecimal) {
                    $decimal .= $key;
                } else {
                    $lastNum = $key;
                }
            }
        }

        return bcmul(bcadd($number, bcadd($partNumber, $lastNum)), $pom) . ($isDecimal ? ('.' . $decimal) : '');
    }

    /**
     * 处理整数部分
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @return int
    */
    private function parseInteger($number) {
        $length = strlen($number);
        $firstItems = $length & 3;
        $leftStr = substr($number, $firstItems);
        if ($leftStr === '' || $leftStr === false) {
            $split4 = [];
        } else {
            $split4 = str_split($leftStr, 4);
        }
        if ($firstItems > 0) {
            array_unshift($split4, substr($number, 0, $firstItems));
        }
        $split4Count = count($split4);

        $unitIndex = ($length - 1) / 4 >> 0;
        if ($unitIndex === 0) {
            $unitIndex = -1;
        } else {
            $unitIndex += 2;
        }

        $result = '';
        foreach ($split4 as $i => $item) {
            $index = $unitIndex - $i;

            $length = strlen($item);

            $itemResult = '';
            $has0 = false;
            for ($j = 0; $j < $length; ++$j) {
                if ($item[$j] == 0) {
                    $has0 = true;
                } else {
                    if ($has0) {
                        $itemResult .= $this->number[0];
                        $has0 = false;
                    }
                    if (!($length == 2 && $j == 0 && $item[$j] == 1))  {
                        $itemResult .= $this->number[$item[$j]];
                    }
                    if ($item[$j] != 0) {
                        $itemResult .= (isset($this->unit[$length - $j - 2]) ? $this->unit[$length - $j - 2] : '');
                    }
                }
            }
            if ($itemResult != '') {
                $result .= $itemResult . (($i != $split4Count - 1 && isset($this->unit[$index])) ? $this->unit[$index] : '');
            }
        }

        return $result;
    }

    /**
     * 处理小数部分
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @return int
    */
    private function parseDecimal($number) {
        if ($number === '') return '';

        $result = '';
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $res = $this->number[$number[$i]];
            $result .= $res;
            if (isset($this->negative[$i]) && $res != '零') {
                $result .= $this->negative[$i];
            }
        }

        return $result;
    }
}
