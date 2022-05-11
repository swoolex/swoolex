<?php
/**
 * +----------------------------------------------------------------------
 * 时间日期常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Time
{
    /**
     * 日期转换成时间戳
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @param string $date 时间格式
     * @return int 时间戳
    */
    public static function dateTurnTime($date) {
        $date = str_replace('/', '-', $date);
        // 年月日格式
        if (strpos($date, '-') !== false) {
            $arr = explode('-', $date);
            // 缺少年
            if (count($arr) == 1) {
                $date = date('Y', time()).'-'.$date;
            }
            return strtotime($date); 
        // 日格式
        } else if (strpos($date, ' ') !== false) {
            $date = date('Y-m', time()).'-'.$date;
            return strtotime($date); 
        }
        // 时分秒格式
        $arr = explode(':', $date);
        $time = strtotime(date('Y-m-d', time()));
        $time += $arr[0]*3600;
        if (isset($arr[1])) $time += $arr[1]*60;
        if (isset($arr[2])) $time += $arr[2];

        return $time;
    }
    /**
     * 时间戳转换成日期
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-06
     * @param int $time 时间戳格式
     * @param string $rule 返回的日期格式
     * @return date 日期
    */
    public static function timeTurnDate($time, $rule='Y-m-d H:i:s') {
        // 秒
        if ($time < 60) {
            $s = strtotime(date('Y-m-d H:i').':00')+$time;
        // 分
        } else if ($time < 3600) {
            $s = strtotime(date('Y-m-d H').':00:00')+$time;
        // 小时
        } else if ($time < 86400) {
            $s = strtotime(date('Y-m-d'))+$time;
        } else {
            // 天
            if ($time < (self::getDay()*86400)) {
                $s = strtotime(date('Y-m-01'))+$time;
            } else {
                // 月
                $day = 365;
                if (self::iSieapYear()) {
                    $day = 366;
                }
                if ($time < ($date*86400)) {
                    $s = strtotime(date('Y-01-01'))+$time;
                } else {
                    $s = $time;
                }
            }
        }
        return date($rule, $s);
    }
    
    /**
     * 获取某月有多少数
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @return int
    */
    public static function getDay($date=null) {
        $date = self::insideDate($date);
        $tem = explode('-' , $date);
        $year = $tem[0];
        $month = $tem[1];
            
        $day = 30;
        if (in_array($month, [1,3,5,7,8,10,12]))  {
            $day = 31;
        } else if ($month == 2 ) {
            $day = 28;
            if ($year%400 == 0 || ($year%4 == 0 && $year%100 !== 0)) {
                $day = 29;
            }
        }
        return $day;
    }

    /**
     * 是否闰年
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @return bool
    */
    public static function iSieapYear($date=null) {
        $date = self::insideDate($date);
        $tem = explode('-' , $date);
        $year = $tem[0];

        if ($year%400 == 0 || ($year%4 == 0 && $year%100 !== 0)) {
            return true;
        }
        return false;
    }

    /**
     * 获取某一天开始的时间戳
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @return int
    */
    public static function startTime($date=null) {
        $date = self::insideDate($date);

        return strtotime($date);
    }

    /**
     * 返回某一天结束的时间戳
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @return int
    */
    public static function endTime($date=null) {
        return self::startTime($date)+86399;
    }

    /**
     * 分解时间日期
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @return array
    */
    public static function dialysis($date=null) {
        $date = self::insideDate($date, 'Y-n-j H:i:s');
        $arr = explode(' ', $date);
        $top = explode('-', $arr[0]);
        $bottom = explode(':', $arr[1]);
        $ret = [
            'year' => $top[0],
            'month' => $top[1],
            'day' => $top[2],
            'hour' => (int)$bottom[0],
            'minute' => (int)$bottom[1],
            'second' => (int)$bottom[2],
            'is_ieap' => self::iSieapYear($date),
            'max_day' => self::getDay($date),
            'timezone' => date_default_timezone_get(),
        ];
        return $ret;
    }

    /**
     * 日期格式美化
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param string|int $date 时间戳或者日期
     * @param string $rule 返回的日期格式
     * @return string
    */
    public static function dateBeautify($time, $rule='Y-m-d H:i:s') {
        if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        $t = time()-$time;
        $f = [
            '31536000'=>'年',
            '2592000'=>'个月',
            '604800'=>'星期',
            '86400'=>'天',
            '3600'=>'小时',
            '60'=>'分钟',
            '1'=>'秒'
        ];
        foreach ($f as $k=>$v)    {
            if (0 !=$c=floor($t/(int)$k)) {
                return $c.$v.'前';
            }
        }
        return date($rule, $time);
    }

    /**
     * 获取当前格林威治时间
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @return string
    */
    public static function gwDate($type=2) {
        switch ($type) {
            case 1: 
                return gmdate('D, d M Y H:i:s \G\M\T'); // Wed, 26 Jun 2021 06:49:24 GMT
            break;
            case 2: 
                $date = new \DateTime('now');
                $date->setTimezone(new \DateTimeZone('UTC'));
                return $date->format('Y-m-d\TH:i:s\Z'); // 2021-09-08T06:40:37Z
            break;
        }
        return false;
    }

    /**
     * 用于获得一个指定格式的日期
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-08
     * @param int $date 时间或日期
     * @param string $rule 返回的日期格式
     * @return string
    */
    private static function insideDate($date=null, $rule='Y-n-d') {
        if (is_numeric($date)) {
            return date($rule, $date);
        } else if (!empty($date)){
            return date($rule, strtotime($date));
        }
        return date($rule);
    }
}