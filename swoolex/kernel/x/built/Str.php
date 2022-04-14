<?php
/**
 * +----------------------------------------------------------------------
 * 字符串常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Str
{
    /**
     * 字符串包含
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iScontain($cover, $condition, $lower=false) {
        if ($lower == true) {
            if (strpos($cover, $condition) !== false) return true;
        } else {
            if (stripos($cover, $condition) !== false) return true;
        }
        return false;
    }

    /**
     * 检查字符串是否以某个字符串开头
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iSstart($cover, $condition, $lower=false) {
        if ($lower == true) {
            if (strpos($cover, $condition) === 0) return true;
        } else {
            if (stripos($cover, $condition) === 0) return true;
        }
        return false;
    }

    /**
     * 检查字符串是否以某个字符串结尾
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $cover 被检查的字符串
     * @param string $condition 包含的字符串
     * @param bool $lower 是否只小写
     * @return bool
    */
    public static function iSend($cover, $condition, $lower=false) {
        $length = strlen($cover)-strlen($condition);
        if ($lower == true) {
            if (strrpos($cover, $condition) === $length) return true;
        } else {
            if (strripos($cover, $condition) === $length) return true;
        }
        return false;
    }

    /**
     * 替换字符串第一次出现的位置
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param string $str 条件字符串
     * @param string $condition 目标字符串
     * @param string $cover 替换的字符串
     * @return string
    */
    public static function replaceStart($str, $condition, $cover) {
        return substr_replace($str, $cover, strpos($str, $condition), strlen($condition));
    }

    /**
     * 获取自定长度的随机字符串
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @param int $type 模式 1.纯数字 2.数+小英 3.数+大英 4.混合
     * @return void
    */
    public static function random($length=16, $type=4) {
        switch ($type) {
            case 1: $str = '0123456789'; $num = 9; break;
            case 2: $str = 'a0sqd1fwg2hej3krl4ztx5cyv6bun7mi8o9p'; $num = 35; break;
            case 3: $str = 'A0SQD1FWG2HEJ3KRL4ZTX5CYV6BUN7MI8O9P'; $num = 35; break;
            case 4: $str = 'qAw0eSrQrtDt1yFyWuGi2oHpEaJs3dKfRgLh4jZkTlXz5xCcYvVb6nBmUN7MI8O9P'; $num = 64; break;
            default:
                return false;
            break;
        }
        $ret = '';
        for ($i=0; $i < $length; $i++) {
            $ret .= $str[mt_rand(0, $num)];
        }
        return $ret;
    }

    /**
     * 获取好看的验证码
     * @todo 无
     * @author 小黄牛
     * @version v2.5.5 + 2021-09-07
     * @deprecated 暂不启用
     * @global 无
     * @param int $length 长度
     * @return int
    */
    public static function smsCode($length=4) {
        if ($length < 4) return false;
        $str = '0123456789'; 
        $num = 9;
        
        // 0.前 1.中 2.后
        $status = mt_rand(0, 2);
        // 双倍数
        $max = $length-3;
        $ret = '';
        for ($i=0; $i < $length; $i++) {
            $ret .= $str[mt_rand(0, $num)];
        }
        // 双倍数替换
        for ($i=0; $i<$max; $i++) {
            $k = $str[mt_rand(0, $num)];
            if ($status == 0) {
                $ret = substr_replace($ret, ($k.$k), 0, 2);
            } else if ($status == 1) {
                $ret = substr_replace($ret, ($k.$k), mt_rand(2, ($length-2)), 2);
            } else if ($status == 2) {
                $ret = substr_replace($ret, ($k.$k), -2, 2);
            }
        }

        return $ret;
    }

    /**
     * 字符串指定位置用*号隐藏
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param int $txt 需要处理的字符串
     * @param int $start 开始位置
     * @param int $end 结束位置
     * @param int $char 替换的字符串
     * @return string
    */
    public static function hide($txt, $start=4, $end=7, $char='*') {
        if ($start < 0 || strlen($txt) < $end) return false;
        
        $start -= 1;
        $num = $end-$start;
        $str = '';
        for ($i=0; $i < $num; $i++) { 
            $str .= $char;
        }

        $strlen = mb_strlen($txt, 'utf-8');
        $firstStr = mb_substr($txt, 0, $start, 'utf-8');
        $lastStr = mb_substr($txt, $end, $strlen, 'utf-8');

        return $firstStr.$str.$lastStr;
    }
    
    /**
     * 随机生成中文名称
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param int $num 随机个数，1个则返回字符串，否则返回数组
     * @return array|string
    */
    public static function randChinese($num=1) {
        if ($num < 1) return false;
        $arrXing = [
            '赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋','沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏','陶','姜','戚','谢','邹',
            '喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭','郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛','雷','贺','倪','汤','滕','殷','罗',
            '毕','郝','安','常','傅','卞','齐','元','顾','孟','平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈','宋','茅','庞','熊','纪','舒','屈','项','祝',
            '董','梁','杜','阮','蓝','闵','季','贾','路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田','樊','胡','凌','霍','虞','万','支','柯','管','卢','莫',
            '柯','房','裘','缪','解','应','宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢','裴','陆','荣','翁','荀','于','惠','甄','曲','封','储','仲','伊',
            '宁','仇','甘','武','符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖','卓','屠','池','乔','胥','闻','莘','党','翟','谭','贡','劳','逄','姬','申',
            '扶','堵','冉','宰','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎','连','习','容','向','古','易','廖','庾','终','步','都','耿','满','弘','匡','国','文',
            '寇','广','禄','阙','东','欧','利','师','巩','聂','关','荆','司马','上官','欧阳','夏侯','诸葛','闻人','东方','赫连','皇甫','尉迟','公羊','澹台','公冶','宗政','濮阳','淳于','单于','太叔',
            '申屠','公孙','仲孙','轩辕','令狐','徐离','宇文','长孙','慕容','司徒','司空'
        ];
        $lenXing = count($arrXing)-1;
        $arrMing = [
            '伟','刚','勇','毅','俊','峰','强','军','平','保','东','文','辉','力','明','永','健','世','广','志','义','兴','良','海','山','仁','波','宁','贵','福','生','龙','元','全'
            ,'国','胜','学','祥','才','发','武','新','利','清','飞','彬','富','顺','信','子','杰','涛','昌','成','康','星','光','天','达','安','岩','中','茂','进','林','有','坚','和','彪','博','诚'
            ,'先','敬','震','振','壮','会','思','群','豪','心','邦','承','乐','绍','功','松','善','厚','庆','磊','民','友','裕','河','哲','江','超','浩','亮','政','谦','亨','奇','固','之','轮','翰'
            ,'朗','伯','宏','言','若','鸣','朋','斌','梁','栋','维','启','克','伦','翔','旭','鹏','泽','晨','辰','士','以','建','家','致','树','炎','德','行','时','泰','盛','雄','琛','钧','冠','策'
            ,'腾','楠','榕','风','航','弘','秀','娟','英','华','慧','巧','美','娜','静','淑','惠','珠','翠','雅','芝','玉','萍','红','娥','玲','芬','芳','燕','彩','春','菊','兰','凤','洁','梅','琳'
            ,'素','云','莲','真','环','雪','荣','爱','妹','霞','香','月','莺','媛','艳','瑞','凡','佳','嘉','琼','勤','珍','贞','莉','桂','娣','叶','璧','璐','娅','琦','晶','妍','茜','秋','珊','莎'
            ,'锦','黛','青','倩','婷','姣','婉','娴','瑾','颖','露','瑶','怡','婵','雁','蓓','纨','仪','荷','丹','蓉','眉','君','琴','蕊','薇','菁','梦','岚','苑','婕','馨','瑗','琰','韵','融','园'
            ,'艺','咏','卿','聪','澜','纯','毓','悦','昭','冰','爽','琬','茗','羽','希','欣','飘','育','滢','馥','筠','柔','竹','霭','凝','晓','欢','霄','枫','芸','菲','寒','伊','亚','宜','可','姬'
            ,'舒','影','荔','枝','丽','阳','妮','宝','贝','初','程','梵','罡','恒','鸿','桦','骅','剑','娇','纪','宽','苛','灵','玛','媚','琪','晴','容','睿','烁','堂','唯','威','韦','雯','苇','萱'
            ,'阅','彦','宇','雨','洋','忠','宗','曼','紫','逸','贤','蝶','菡','绿','蓝','儿','翠','烟'
        ];
        $lenMing = count($arrMing)-1;

        $ret = [];
        for ($i=1; $i <= $num; $i++) { 
            $randXing = random_int(0, $lenXing);
            $name = $arrXing[$randXing];
            $max = random_int(1, 2);
            for ($k=1; $k <= $max; $k++) { 
                $randMing = random_int(0, $lenMing);
                $name .= $arrMing[$randMing];
            }
            $ret[] = $name;
        }

        if (count($ret) == 1) return current($ret);

        return $ret;
    }

    /**
     * 随机生成手机号
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param int $num 随机个数，1个则返回字符串，否则返回数组
     * @return array|string
    */
    public static function randPhone($num=1) {
        if ($num < 1) return false;

        $telArr = [
            '130','131','132','133','134','135','136','137','138','139','144','147','150','151','152','153','155','156','157','158','159','176','177','178','180','181','182','183','184','185','186','187','188','189',
        ];
        $lenTel = count($telArr)-1;
        
        $ret = [];
        for ($i=1; $i <= $num; $i++) { 
            $rand = random_int(0, $lenTel);
            $ret[] = $telArr[$rand].random_int(1000,9999).random_int(1000,9999);
        }

        if (count($ret) == 1) return current($ret);

        return $ret;
    }

    /**
     * 随机生成邮箱
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param int $num 随机个数，1个则返回字符串，否则返回数组
     * @param bool $qq 是否只生成qq的邮箱
     * @return array|string
    */
    public static function randEmail($num=1, $qq=false) {
        if ($num < 1) return false;

        $mailArr = [
            '126.com','163.com','sina.com','21cn.com','sohu.com','yahoo.com.cn','tom.com','qq.com','etang.com','eyou.com','56.com','chinaren.com',
            'sogou.com','citiz.com','hotmail.com','msn.com','yahoo.com','gmail.com','aim.com','aol.com','mail.com','walla.com','inbox.com','gmail.com',
            'yahoo.com','msn.com','hotmail.com','ask.com','live.com','0355.net','263.net','3721.net','yeah.net','googlemail.com'
        ];
        $strArr = ['a','b','c','d','e','f','g','h','j','k','l','n','m','o','p','q'];
        $lenMail = count($mailArr)-1;
        $lenStr = count($strArr)-1;
        
        $ret = [];
        for ($i=1; $i <= $num; $i++) { 
            $rand = random_int(0, $lenMail);
            $suffix = $qq ? 'qq.com' : $mailArr[$rand];
            
            $mail = '';
            // QQ的特殊处理
            if ($suffix == 'qq.com') {
                $rand = random_int(6, 12);
                for ($k=0; $k < $rand; $k++) { 
                    $mail .= random_int(0, 9);
                }
            } else {
                $max = random_int(9, 13);
                $strMax = random_int(3, 7);
                $intMax = $max-$strMax;
                for ($k=0; $k < $strMax; $k++) { 
                    $rand = random_int(0, $lenStr);
                    $mail .= $strArr[$rand];
                }
                for ($k=0; $k < $intMax; $k++) { 
                    $mail .= random_int(0, 9);
                }
            }

            $ret[] = $mail.'@'.$suffix;
        }

        if (count($ret) == 1) return current($ret);

        return $ret;
    }
}