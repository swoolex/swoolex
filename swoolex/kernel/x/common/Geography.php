<?php
/**
 * +----------------------------------------------------------------------
 * 地理位置
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\common;

use \x\common\geography\Lbs;
use \x\common\geography\Correct;

class Geography
{
    /**
     * 百度转腾讯/高德坐标
     * 
     * @param float $longitude	经度
     * @param float $latitude	纬度
     * @return array
     */
    public static function baidu_to_tencent($longitude, $latitude) {
        $x = (float)$longitude - 0.0065;
        $y = (float)$latitude - 0.006;
        $x_pi = 3.14159265358979324 * 3000 / 180;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $gb = number_format($z * cos($theta), 15);
        $ga = number_format($z * sin($theta), 15);
        return ['longitude' => $gb, 'latitude' => $ga];
    }

    /**
     * 腾讯/高德转百度坐标
     * 
     * @param float $longitude	经度
     * @param float $latitude	纬度
     * @return array
     */
    public static function tencent_to_baidu($longitude, $latitude) {
        $x = (float)$longitude;
        $y = (float)$latitude;
        $x_pi = 3.14159265358979324 * 3000 / 180;
        $z = sqrt($x * $x + $y * $y) + 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) + 0.000003 * cos($x * $x_pi);
        $gb = number_format($z * cos($theta) + 0.0065, 6);
        $ga = number_format($z * sin($theta) + 0.006, 6);
        return ['longitude' => $gb, 'latitude' => $ga];
    }

    /**
     * 计算两点之间的直线距离
     * 
     * @param float $longitude1 起点经度
     * @param float $latitude1 起点纬度
     * @param float $longitude2 终点经度
     * @param float $latitude2 终点纬度
     * @param string $unit 单位 km.公里 m.米
     * @return float 
    */
    public static function distance($longitude1, $latitude1, $longitude2, $latitude2, $unit='km'){  
        $theta = $longitude1 - $longitude2;
        $miles = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $distance = $miles * 1.609344;
        if (strtolower($unit) == 'm') {
            $distance = $distance * 1000;
        }
        return round($distance, 4);
    } 
    
    /**
     * 判断经纬度是否在地理围栏内
     * 
     * @param float $longitude	经度
     * @param float $latitude	纬度
     * @param array $list 围栏的经纬度集合
     * @return bool
    */
    public static function is_fence($longitude, $latitude, $list) {
        $obj = new Lbs();
        $res = $obj->is_polygon(['longitude'=>$longitude, 'latitude'=>$latitude], $list);
        unset($obj);
        return $res;
    }

    /**
     * 判断经纬度是否在中国大陆内
     * 
     * @param float $longitude	经度
     * @param float $latitude	纬度
     * @return bool
    */
    public static function is_chinese_mainland($longitude, $latitude) {
        $list = array(
            array('longitude'=>87.467096,'latitude'=>49.203257),array('longitude'=>85.553777,'latitude'=>48.425652),array('longitude'=>85.112242,'latitude'=>47.23644),array('longitude'=>83.440554,'latitude'=>47.347075),array('longitude'=>82.410305,'latitude'=>47.347075),array('longitude'=>81.96877,'latitude'=>45.515516),array('longitude'=>80.055451,'latitude'=>45.100082),array('longitude'=>80.349808,'latitude'=>43.192693),array('longitude'=>79.908273,'latitude'=>42.215737),array('longitude'=>75.198565,'latitude'=>40.777287),array('longitude'=>73.285246,'latitude'=>39.535065),array('longitude'=>73.87396,'latitude'=>38.270024),array('longitude'=>74.75703,'latitude'=>38.03758),array('longitude'=>74.168316,'latitude'=>37.335775),array('longitude'=>74.021138,'latitude'=>36.745837),array('longitude'=>75.787278,'latitude'=>36.270565),array('longitude'=>76.081635,'latitude'=>35.552157),array('longitude'=>77.847776,'latitude'=>35.190491),array('longitude'=>78.583668,'latitude'=>32.986494),array('longitude'=>77.994954,'latitude'=>32.239068),array('longitude'=>78.730846,'latitude'=>30.979485),array('longitude'=>86.5313,'latitude'=>27.363464),array('longitude'=>88.150262,'latitude'=>27.363464),array('longitude'=>89.474867,'latitude'=>26.704439),array('longitude'=>90.063581,'latitude'=>27.756991),array('longitude'=>92.124078,'latitude'=>26.307159),array('longitude'=>94.184575,'latitude'=>26.572167),array('longitude'=>96.245073,'latitude'=>27.625974),array('longitude'=>97.569678,'latitude'=>27.100324),array('longitude'=>98.011213,'latitude'=>27.100324),array('longitude'=>97.422499,'latitude'=>24.435158),array('longitude'=>97.569678,'latitude'=>23.216561),array('longitude'=>98.894283,'latitude'=>22.39783),array('longitude'=>100.218889,'latitude'=>20.884182),array('longitude'=>102.132207,'latitude'=>20.468623),array('longitude'=>102.573743,'latitude'=>21.849274),array('longitude'=>105.664488,'latitude'=>22.53463),array('longitude'=>107.577807,'latitude'=>21.022445),array('longitude'=>109.343948,'latitude'=>20.884182),array('longitude'=>107.430629,'latitude'=>18.795166),array('longitude'=>109.196769,'latitude'=>17.104703),array('longitude'=>122.148466,'latitude'=>21.436452),array('longitude'=>131.126347,'latitude'=>36.389659),array('longitude'=>131.420704,'latitude'=>35.912183),array('longitude'=>131.862239,'latitude'=>43.084907),array('longitude'=>132.009418,'latitude'=>44.576479),array('longitude'=>131.862239,'latitude'=>44.891216),array('longitude'=>133.481201,'latitude'=>44.576479),array('longitude'=>135.836055,'latitude'=>48.435918),array('longitude'=>135.247342,'latitude'=>48.923243),array('longitude'=>131.567882,'latitude'=>47.943845),array('longitude'=>131.273526,'latitude'=>49.020139),array('longitude'=>127.888423,'latitude'=>50.073522),array('longitude'=>126.47139,'latitude'=>52.93675),array('longitude'=>124.410893,'latitude'=>53.554725),array('longitude'=>122.056039,'latitude'=>53.773261),array('longitude'=>120.363488,'latitude'=>53.335049),array('longitude'=>119.701185,'latitude'=>52.669153),array('longitude'=>120.216309,'latitude'=>52.038307),array('longitude'=>117.935045,'latitude'=>49.758538),array('longitude'=>116.463261,'latitude'=>49.996322),array('longitude'=>115.212245,'latitude'=>48.159531),array('longitude'=>115.800958,'latitude'=>47.415619),array('longitude'=>117.051974,'latitude'=>47.565259),array('longitude'=>118.229401,'latitude'=>47.764111),array('longitude'=>119.25965,'latitude'=>47.064791),array('longitude'=>114.844299,'latitude'=>46.254112),array('longitude'=>113.666872,'latitude'=>45.119433),array('longitude'=>111.97432,'latitude'=>45.223543),array('longitude'=>111.017661,'latitude'=>44.596007),array('longitude'=>111.017661,'latitude'=>43.69519),array('longitude'=>105.056937,'latitude'=>42.016915),array('longitude'=>100.862353,'latitude'=>42.996992),array('longitude'=>96.814948,'latitude'=>42.942949),array('longitude'=>96.079056,'latitude'=>44.279652),array('longitude'=>93.503434,'latitude'=>45.379349),array('longitude'=>91.222169,'latitude'=>45.431189),array('longitude'=>91.590115,'latitude'=>47.064791),array('longitude'=>90.118332,'latitude'=>48.649527),array('longitude'=>88.42578,'latitude'=>49.663095),array('longitude'=>87.321943,'latitude'=>49.183045)
        );
        return self::is_fence($longitude, $latitude, $list);
    }

    /**
     * 经纬度纠偏
     * 
     * @param array $list 经纬度集合
     * @return array 纠偏结果
    */
    public static function longitude_latitude_correction($list) {
        $obj = new Correct();
        $res = $obj->run($list);
        unset($obj);
        return $res;
    }
}