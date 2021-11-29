<?php
/**
 * +----------------------------------------------------------------------
 * 地理围栏过滤判断
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\common\geography;

use \x\common\Geography;

class Lbs {

    // 判断一个坐标是否在圆内
    public function is_circle($point, $circle){
        $distance = Geography::distance($point['latitude'],$point['longitude'],$circle['center']['latitude'],$circle['center']['longitude']);
        if ($distance <= $circle['radius']) return true;
        return false;
    }
    // 判断一个坐标是否在一个多边形内（由多个坐标围成的）
    public function is_polygon($point, $pts) {
        $N = count($pts);
        $boundOrVertex = true;
        $intersectCount = 0;
        $precision = 2e-10;
        $p1 = 0;
        $p2 = 0;
        $p = $point;
        $p1 = $pts[0];
        for ($i = 1; $i <= $N; ++$i) {
            if ($p['longitude'] == $p1['longitude'] && $p['latitude'] == $p1['latitude']) {
                return $boundOrVertex;
            }
            $p2 = $pts[$i % $N];
            if ($p['latitude'] < min($p1['latitude'], $p2['latitude']) || $p['latitude'] > max($p1['latitude'], $p2['latitude'])) {
                $p1 = $p2;
                continue;
            }
            if ($p['latitude'] > min($p1['latitude'], $p2['latitude']) && $p['latitude'] < max($p1['latitude'], $p2['latitude'])) {
                if($p['longitude'] <= max($p1['longitude'], $p2['longitude'])){
                    if ($p1['latitude'] == $p2['latitude'] && $p['longitude'] >= min($p1['longitude'], $p2['longitude'])) {
                        return $boundOrVertex;
                    }
                    if ($p1['longitude'] == $p2['longitude']) {
                        if ($p1['longitude'] == $p['longitude']) {
                            return $boundOrVertex;
                        } else {
                            ++$intersectCount;
                        }
                    } else {
                        $xinters = ($p['latitude'] - $p1['latitude']) * ($p2['longitude'] - $p1['longitude']) / ($p2['latitude'] - $p1['latitude']) + $p1['longitude'];
                        if (abs($p['longitude'] - $xinters) < $precision) {
                            return $boundOrVertex;
                        }
                        if ($p['longitude'] < $xinters) {
                            ++$intersectCount;
                        }
                    }
                }
            } else {
                if ($p['latitude'] == $p2['latitude'] && $p['longitude'] <= $p2['longitude']) {
                    $p3 = $pts[($i+1) % $N];
                    if ($p['latitude'] >= min($p1['latitude'], $p3['latitude']) && $p['latitude'] <= max($p1['latitude'], $p3['latitude'])) {
                        ++$intersectCount;
                    } else {
                        $intersectCount += 2;
                    }
                }
            }
            $p1 = $p2;
        }
        if ($intersectCount % 2 == 0) return false;
        return true;
    }
}