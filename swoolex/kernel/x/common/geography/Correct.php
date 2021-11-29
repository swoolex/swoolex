<?php
/**
 * +----------------------------------------------------------------------
 * 经纬度纠偏算法
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

class Correct {
	public $maximum_speed = 36;
    public $minnum_speed = 10;
    public $min_spot_num = 20;
    public $min_distance_time = 180;
    public $max_middle_angle = 6;
    public $max_start_angle = 17;
    public $max_end_angle = 17;
	public $baseline_rate = 65;
	public $drift_error_rate = 40;
	public $negative_rate = 0;
	public $drift_rate = 0;
	public $angle_rate = 0;
    public $error_rate = 0;
    public $num = 0;
	public $list2 = [];
	public $list3 = [];

	public function run($list) {
		$this->Anum = count($list);
		$this->list2 = $list;

		$status = false;
		foreach ($this->list2 as $key=>$val) {
			$bool = Geography::is_chinese_mainland($val['longitude'], $val['latitude']);
			if (!$bool) {
				$this->num++;
				unset($this->list2[$key]);
			}
		}
		if ($status) $this->list2 = $this->array_valuess($this->list2);

		$this->scene1();
		$this->scene2();
		$this->scene3();
		$this->scene4();
		$this->scene5();
		$this->scene6();
		$this->scene7();
		$this->scene8();
		$this->scene9();
		$this->scene10();
		$this->scene11();

		$km = 0;
		$i = 0;
		foreach ($this->list2 as $key=>$val) {
			if ($i > 0) {
				$km += Geography::distance($lng, $lat, $val['longitude'], $val['latitude']);
			}
			$lat=$val['latitude'];
			$lng=$val['longitude'];
			$i++;
		}
		
		$return['list'] = $this->list2;
		$return['num_rate'] = round((100 / $this->Anum ) * $this->num, 2);
		$return['km'] = $km;
		return $return;
	}
	
	private function array_valuess($array) {
		$list = array();
		foreach ($array as $v) {
			$list[] = $v;
		}
		return $list;
	}
	
	private function angle($one, $two, $three) {
		$px0 = $one['longitude'];
		$py0 = $one['latitude'];
		$pxv = $two['longitude'];
		$pyv = $two['latitude'];								
		$px1 = $three['longitude'];
		$py1 = $three['latitude'];								
        $vector = ($px0-$pxv)*($px1-$pxv)+($py0-$pyv)*($py1-$pyv);
        if ($vector == 0) return 0;
		$psqrt = sqrt((abs(($px0-$pxv)*($px0-$pxv))+abs(($py0-$pyv)*($py0-$pyv)))*(abs(($px1-$pxv)*($px1-$pxv))+abs(($py1-$pyv)*($py1-$pyv))));
		return (int)rad2deg(acos($vector/$psqrt));
	}

	private function scene1() {
		$negative = 0;
		$error_num = 0;
		foreach ($this->list2 as $k=>$v) {
			$lng = sprintf("%1\$.3f", $v['longitude']);
			$lat = sprintf("%1\$.3f", $v['latitude']);
			$latlng = $lat.','.$lng;

			$this->list3[$latlng][] = $k;

			if (isset($v['speed']) && $v['speed'] < 0) {
				$negative++;
			}

			if (isset($v['error_circle']) && $v['error_circle'] >= 200) {
				$error_num++;
			}

			if ($k >= 2) {
				$angle = $this->angle($this->list2[$k-2], $this->list2[$k-1], $v);
				$arr = $this->list2[$k-1];
				$spot = $arr['latitude'].','.$arr['longitude'];
				$this->original_angle[$spot] = $angle;
			}
		}
		$this->negative_rate = round((100 / count($this->list2) ) * $negative, 2);
		$this->error_rate = round((100 / count($this->list2) ) * $error_num, 2);
	}
	
	private function scene2() {
		$length  = count($this->list2)-10;
		$length2 = count($this->list2)*0.8;
		$status  = false;
		
		$rate = 100 / count($this->list3);
		$k = 0;
		foreach ($this->list3 as $v) {
			if (count($v) > 10) {
				$k++;
			}
		}
		$this->drift_rate = round(($rate*$k), 2);
		if ($this->drift_rate > $this->drift_error_rate) {
			return false;
		}

		$time = 0;
		foreach ($this->list3 as $k=>$v) {
			$num = count($v);
			if ($num > 10) {
				$status = true;
				foreach ($v as $kk=>$key) {
					if ($key >= $length) {
						$time = $this->list2[$key]['create_time'];
						break;
					}
					if ($num < 25 && $kk == 0) {
						$time = $this->list2[$key]['create_time'];
						break;
					}
					if ($time) {
						if (($this->list2[$key]['create_time']-$time) > 60) {
							$this->num++;
							unset($this->list2[$key]);
						}
					} else {
						$this->num++;
						unset($this->list2[$key]);
					}
					
					$time = $this->list2[$key]['create_time'];
				}
			} else if ($num >= 3 && $num <= 9) {
				$status = true;
				foreach ($v as $kk=>$key) {
					if ($key <= $length2) {
						$time = $this->list2[$key]['create_time'];
						break;
					}
					if ($kk >= ($num-1)) {
						$time = $this->list2[$key]['create_time'];
						break;
					}
					if ($time) {
						if (($this->list2[$key]['create_time']-$time) > 60) {
							$this->num++;
							unset($this->list2[$key]);
						}
					} else {
						$this->num++;
						unset($this->list2[$key]);
					}
					
					$time = $this->list2[$key]['create_time'];
				}
			}
		}
		if ($status) $this->list2 = $this->array_valuess($this->list2);
	}
	
	private function scene3() {
		$this->list2 = $this->array_valuess($this->list2);
		$num = count($this->list2) * 0.8;
		foreach ($this->list2 as $k=>$v) {
			if ($k > 30 && $k < $num && $this->error_rate > 4 && $this->error_rate < 10) {
				if (isset($v['error_circle']) && $v['error_circle'] >= 300) {
					$this->num++;	
					unset($this->list2[$k]);
					continue;
				}
				if (isset($v['error_circle']) && isset($v['speed']) && $v['error_circle'] >= 200 && $v['speed'] <= 10) {	
					$this->num++;
					unset($this->list2[$k]);
					continue;
				}
			}
			if (isset($v['speed']) && $k > 10 && $k < $num && $this->negative_rate < 30 && $v['speed']<0) { 
				$this->num++;
				unset($this->list2[$k]);
				continue;
			}	
		}
	}
	
	private function scene4() {
		$this->list2 = $this->array_valuess($this->list2);
		$num = count($this->list2);

		$start = 3;$end = ($num-3);

		$list  = array();
		foreach ($this->list2 as $k=>$v) {
			if ($k > 0) {
				$s = $v['create_time'] - $this->list2[$k-1]['create_time'];
				$km = Geography::distance($this->list2[$k-1]['longitude'], $this->list2[$k-1]['latitude'], $v['longitude'], $v['latitude']);
				$lat = $v['latitude']-$this->list2[$k-1]['latitude'];
				$lng = $v['longitude']-$this->list2[$k-1]['longitude'];
				$hour_spot = $v;

				$spot = $v['latitude'].','.$v['longitude'];
				if (!empty($this->original_angle[$spot]) && $this->original_angle[$spot] <= 0.001) { 
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
				if ($k > $start && $k < $end) {
					if (($km*1000) > ($s*$this->maximum_speed)) { 
						$list[] = array(
							'key' => $k,
						);
						continue;
					}

					if ($km != 0 && ($km*1000) < $this->minnum_speed) {	
						$list[] = array(
							'key' => $k,
						);
						continue;	
					}
				}
			}
		}
		foreach ($list as $v) {
			$this->num++;
			unset($this->list2[$v['key']]);
		}
	}
	
	private function scene5() {
		$this->list2 = $this->array_valuess($this->list2);
		$num = 0;
		$error_lat = false;
		$count = count($this->list2)-1;
		foreach ($this->list2 as $k=>$v) {
			if ($k >= 2 && $k != $count) {
				$angle = $this->angle($this->list2[$k-1], $v, $this->list2[$k+1]);
				$this->list2[$k]['angle'] = $angle;
				if ($angle != 0 && $angle < $this->max_middle_angle) {
					$num++;
				}
				$spot = $this->list2[$k]['latitude'].','.$this->list2[$k-1]['longitude'];
				if ($spot == $error_lat) {
					$this->list2[$k]['delete'] = 1;
				} else {
					$error_lat = false;
					if ($angle != 0 && $angle < $this->max_middle_angle) {
						$this->list2[$k]['delete'] = 1;
						$error_lat = $spot;
					}
				}
			}
		}
		$ret = 100 / count($this->list2);
		$this->angle_rate = round($ret*$num, 2);
		if ($this->angle_rate <= $this->baseline_rate) {
			foreach ($this->list2 as $k=>$v) {
				if (isset($v['delete'])) {
					$this->num++;
					unset($this->list2[$k]);
				}
			}
		} else {
			$count = count($this->list2);
			$start_num = $count * 0.1;
			$end_num = $count * 0.9;
			
			foreach ($this->list2 as $k=>$v) {
				if ( (($k <= $start_num) || ($k >= $end_num)) && isset($v['delete'])) {
					$this->num++;
					unset($this->list2[$k]);
				}
			}
		}
	}
	
	private function scene6() {
		$this->list2 = $this->array_valuess($this->list2);
		$count = count($this->list2)-1;
		foreach ($this->list2 as $k=>$v) {
			if ($k==0 || $k == $count) {
				$this->list2[$k]['angle'] = 0;
			} else {
				$angle = $this->angle($this->list2[$k-1], $v, $this->list2[$k+1]);
				$this->list2[$k]['angle'] = $angle;
			}
		}
		$status = false;
		foreach ($this->list2 as $k=>$v) {
			if ($k == 1) {
				$this->list2[$k]['delete'] = 1;
				$status = true;
			} else if ($k > 1 && $k<$count) {
				if (
					$this->list2[$k-1]['angle'] >= 65 && 
					$this->list2[$k+1]['angle'] >= 65 && 
					$v['angle'] <= 35
				) {
					$this->list2[$k]['delete'] = 1;
					$status = true;
				} else if (
					$this->list2[$k-1]['angle'] <= 35 && 
					$this->list2[$k+1]['angle'] >= 65 && 
					$v['angle'] <= 35
				) {
					$this->list2[$k]['delete'] = 1;
					$status = true;
				} else if (
					$this->list2[$k-1]['angle'] >= 65 && 
					$this->list2[$k+1]['angle'] <= 35 && 
					$v['angle'] <= 35
				) {
					$this->list2[$k]['delete'] = 1;
					$status = true;
				}
			}
		}
		
		if ($status) {
			foreach ($this->list2 as $k=>$v) {
				if (isset($v['delete']) && $v['angle'] != 0) {
					$this->num++;
					unset($this->list2[$k]);
				}
			}
			$this->list2 = $this->array_valuess($this->list2);
		}
	}
	
	private function scene8(){
		$count = count($this->list2);
		$start = $count * 0.3;
		$end = $count * 0.8;

		$top_km = 0;
		$list = array();
		if ($count > 30) {
			$this->list2 = $this->array_valuess($this->list2);
			$list = array();
			foreach ($this->list2 as $k=>$v) {
				if ($k > 0 && $k > $start && $k < $end) {
					$km = Geography::distance($this->list2[$k-1]['longitude'], $this->list2[$k-1]['latitude'], $v['longitude'], $v['latitude']);
					$s = $v['create_time']-$this->list2[$k-1]['create_time'];
					$speed_km = ($km*1000)/$s;
					if ($top_km >= 11 && $speed_km >= 11) {
						$angle = $this->angle($this->list2[$k-1], $v, $this->list2[$k+1]);
						if ($angle < 60) {
							$list[] = array(
								'key' => $k,
							);
							$top_km = $speed_km;
							continue;
						}
					}
					$top_km = $speed_km;

					$time4 = $this->list2[$k-3]['create_time']-$this->list2[$k-4]['create_time'];
					$time3 = $this->list2[$k-2]['create_time']-$this->list2[$k-3]['create_time'];
					$time2 = $this->list2[$k-1]['create_time']-$this->list2[$k-2]['create_time'];
					$time1 = $this->list2[$k]['create_time']-$this->list2[$k]['create_time'];

					if ($time4 > 30 || $time3 > 30 || $time2 > 30 || $time1 > 30) {
						continue;
					}

					$start_lat = round($this->list2[$k-1]['latitude'], 4);
					$lat = round($v['latitude'], 4);
					$end_lat = round($this->list2[$k+1]['latitude'], 4);
					$start_differ = (float)ltrim(round($start_lat-$lat, 4), '-');
					$end_differ = (float)ltrim(round($lat-$end_lat, 4), '-');
					if ($start_differ >= 0.005 || $end_differ >= 0.005) {
						$list[] = array(
							'key' => $k,
						);
					} else {
						$start_lng = round($this->list2[$k-1]['longitude'], 4);
						$lng = round($v['longitude'], 4);
						$end_lng = round($this->list2[$k+1]['longitude'], 4);
						$start_differ = (float)ltrim(round($start_lng-$lng, 4), '-');
						$end_differ = (float)ltrim(round($lng-$end_lng, 4), '-');
						if ($start_differ >= 0.005 || $end_differ >= 0.005) {
							$list[] = array(
								'key' => $k,
							);
						}
					}
				}
			}

			foreach ($list as $v) {
				$this->num++;
				unset($this->list2[$v['key']]);
			}
		}
	}
	private function scene7() {
		$list = array();
		foreach ($this->list2 as $k=>$v) {
			$spot = $v['latitude'].','.$v['longitude'];
			$list[$spot][] = $k;
		}
		foreach ($list as $spot=>$k) {
			arsort($k);
			array_shift($k);
			foreach ($k as $key) {
				$this->num++;
				unset($this->list2[$key]);
			}
		}
	}
	
	private function scene9() {
		$this->list2 = $this->array_valuess($this->list2);
		$num = count($this->list2);
		$start = $num * 0.3;
		$end = $num * 0.7;

		$list = array();
		foreach ($this->list2 as $k=>$v) {
			if ($k > $start && $k < $end) {
				$km = Geography::distance($this->list2[0]['longitude'], $this->list2[0]['latitude'], $v['longitude'], $v['latitude']);
				$angle = $this->angle($this->list2[$k+1], $v, $this->list2[$k-1]);
				if ($km < 0.7 && $angle <= $this->max_start_angle) {
					$list[] = array(
						'key' => $k,
					);
				} else if ($angle <= $this->max_start_angle) {
					$list[] = array(
						'key' => $k,
					);
				}
			}
		}
		foreach ($list as $v) {
			$this->num++;
			unset($this->list2[$v['key']]);
		}
	}
	
	private function scene10() {
		$this->list2 = $this->array_valuess($this->list2);
		$count = count($this->list2);
		if ($count < 20) {$num = 7;}
		else if ($count < 40) {$num = 20;}
		else {$num = ceil($count * 0.3);}

		$list = array();
		foreach ($this->list2 as $k=>$v) {
			if ($k > $num) break;
			if ($k == 0) {
				$s = $this->list2[$k+1]['create_time']-$v['create_time'];
				$km = Geography::distance($v['longitude'], $v['latitude'], $this->list2[$k+1]['longitude'], $this->list2[$k+1]['latitude']);
				if (($km*1000) > ($s*$this->maximum_speed)) { 
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
				$angle = $this->angle($v, $this->list2[$k+1], $this->list2[$k+2]);
				if ($angle <= $this->max_start_angle) {
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
			} else {
				$s = $v['create_time']-$this->list2[$k-1]['create_time'];
				$km = Geography::distance($this->list2[$k-1]['longitude'], $this->list2[$k-1]['latitude'], $v['longitude'], $v['latitude']);
				if (($km*1000) > ($s*$this->maximum_speed)) { 
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
				if (isset($this->list2[$k+1])) {
					$angle = $this->angle($this->list2[$k-1], $v, $this->list2[$k+1]);
					if ($angle <= $this->max_start_angle) {
						$list[] = array(
							'key' => $k,
						);
						continue;
					}
				}
			}
		}

		foreach ($list as $v) {
			if (isset($this->list2[$v['key']])) {
				$this->num++;
				unset($this->list2[$v['key']]);
			}
		}
	}
	
	private function scene11() {
		$this->list2 = $this->array_valuess($this->list2);
		$num = count($this->list2)-1;
		$count = ceil($num * 0.7);

		$list = array();
		$next_s = 0;
		$next_km = 0;

		$end_km = Geography::distance($this->list2[0]['longitude'], $this->list2[0]['latitude'], $this->list2[$num]['longitude'], $this->list2[$num]['latitude']);
		
		for ($k=$num; $k>=$count; $k--) {
			if ($k > ($num-5)) {
				$km = Geography::distance($this->list2[0]['longitude'], $this->list2[0]['latitude'], $this->list2[$k]['longitude'], $this->list2[$k]['latitude']);
				if ($km < 3) {
					$s =  $this->list2[$k]['create_time']-$this->list2[$k-1]['create_time'];
					$km = Geography::distance($this->list2[$k-1]['longitude'], $this->list2[$k-1]['latitude'], $this->list2[$k]['longitude'], $this->list2[$k]['latitude']);
					if ($s > 360 && $km >= 2) {
						$list[] = array(
							'key' => $k,
						);
						continue;
					} else {
						if ($next_s != 0 && $next_km != 0) {
							if ($s >= 60 && $km >= 1.5 && $next_s < 60 && $next_km < 0.5) {
								$list[] = array(
									'key' => $k,
								);
							}
						}
						$next_s = $s;
						$next_km = $km;
					}
				}
			}
			if ($end_km > 6) {
				$km = Geography::distance($this->list2[0]['longitude'], $this->list2[0]['latitude'], $this->list2[$k]['longitude'], $this->list2[$k]['latitude']);
				if ($km < 1.5) {
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
			}

			$s =  $this->list2[$k]['create_time']-$this->list2[$k-1]['create_time'];
			$km = Geography::distance($this->list2[$k-1]['longitude'], $this->list2[$k-1]['latitude'], $this->list2[$k]['longitude'], $this->list2[$k]['latitude']);
			if (($km*1000) > ($s*$this->maximum_speed)) { 
				$list[] = array(
					'key' => $k,
				);
				continue;
			}
			if ($k < $num) {
				$angle = $this->angle($this->list2[$k+1], $this->list2[$k], $this->list2[$k-1]);
				if ($angle <= $this->max_start_angle) {
					$list[] = array(
						'key' => $k,
					);
					continue;
				}
			}
		}

		foreach ($list as $v) {
			$this->num++;
			unset($this->list2[$v['key']]);
		}
	}
}
