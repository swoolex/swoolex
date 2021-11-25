<?php
/**
 * +----------------------------------------------------------------------
 * 金额分隔成随机红包
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common\money;

class RedPacket
{
    /**
     * 红包列表
    */
    private $amountArr = [];

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
    public function handle($money, $num, $minMoney) {
        if ($minMoney !== null && ($money < $validAmount = $minMoney * $num)) {
            throw new Exception('红包总金额必须 ≥ '.$validAmount.'元');
            return false;
        }

        $list = [];
        for ($i = 1; $i <= $num; $i++) {
            $remain = $money - array_sum($list) - ($num - $i + 1) * $minMoney;

            if ($i < $num) {
                $get = $this->random_float(0, $remain / ($num - $i + 1) * 2);
            } else {
                $get = $remain;
            }

            $list[] = \x\common\Money::round(round($get, 2) + $minMoney, 2);
        }

        return $list;
    }

    // 产生一个随机浮点数
    private function random_float($min = 0, $max = 1) {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), 2);
    }
}
