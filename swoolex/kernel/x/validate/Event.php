<?php
/**
 * +----------------------------------------------------------------------
 * 验证器处理事件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\validate;

class Event
{
    /**
     * 事件注册队列
    */
    public static $list = [
        'require' => ['class'=>['\x\validate\rule\requires'],         'message'=>'{:preset}必填'],
        'accepted' => ['class'=>['\x\validate\rule\accepted'],        'message'=>'{:preset}必须是 yes、on、1、或 true'],
        'number' => ['class'=>['\x\validate\rule\numbers'],           'message'=>'{:preset}必须是纯数字'],
        'int' => ['class'=>['\x\validate\rule\ints'],                 'message'=>'{:preset}必须是整数'],
        'float' => ['class'=>['\x\validate\rule\floats'],             'message'=>'{:preset}必须是浮点数字'],
        'bool' => ['class'=>['\x\validate\rule\bools'],               'message'=>'{:preset}必须是布尔值'],
        'array' => ['class'=>['\x\validate\rule\arrays'],             'message'=>'{:preset}必须是数组'],
        'email' => ['class'=>['\x\validate\rule\email'],              'message'=>'{:preset}不是正确的邮箱格式'],
        'mobile'=> ['class'=>['\x\validate\rule\mobile'],             'message'=>'{:preset}不是正确的国内电话号码'],
        'phone'=> ['class'=>['\x\validate\rule\phone'],               'message'=>'{:preset}不是正确的手机号码'],
        'date'=> ['class'=>['\x\validate\rule\dates'],                'message'=>'{:preset}不是正确的日期格式'],
        'alpha'=> ['class'=>['\x\validate\rule\alpha'],               'message'=>'{:preset}必须是纯字母'],
        'alphanum'=> ['class'=>['\x\validate\rule\alphaNum'],         'message'=>'{:preset}必须是字母或数字'],
        'alphadash'=> ['class'=>['\x\validate\rule\alphaDash'],       'message'=>'{:preset}必须是字母或数字、或下划线_及破折号-'],
        'chs'=> ['class'=>['\x\validate\rule\chss'],                  'message'=>'{:preset}必须是纯汉字'],
        'chsalpha'=> ['class'=>['\x\validate\rule\chsAlpha'],         'message'=>'{:preset}必须是汉字或字母'],
        'chsalphanum'=> ['class'=>['\x\validate\rule\chsAlphaNum'],   'message'=>'{:preset}必须是汉字、字母或数字'],
        'cntrl'=> ['class'=>['\x\validate\rule\cntrl'],               'message'=>'{:preset}必须是控制字符（换行、缩进、空格）'],
        'graph'=> ['class'=>['\x\validate\rule\graph'],               'message'=>'{:preset}必须是可打印字符（空格、换行除外）'],
        'print'=> ['class'=>['\x\validate\rule\prints'],              'message'=>'{:preset}必须是可打印字符（包括空格）'],
        'lower'=> ['class'=>['\x\validate\rule\lower'],               'message'=>'{:preset}必须是小写字母'],
        'upper'=> ['class'=>['\x\validate\rule\upper'],               'message'=>'{:preset}必须是大写字母'],
        'space'=> ['class'=>['\x\validate\rule\space'],               'message'=>'{:preset}必须是空白字符（包括缩进，垂直制表符，换行符，回车和换页字符）'],
        'xdigit'=> ['class'=>['\x\validate\rule\xdigit'],             'message'=>'{:preset}必须是十六进制字符串'],
        'url'=> ['class'=>['\x\validate\rule\url'],                   'message'=>'{:preset}必须是为有效的URL地址'],
        'ip'=> ['class'=>['\x\validate\rule\ip'],                     'message'=>'{:preset}必须是为有效的IP地址'],
        'dateformat'=> ['class'=>['\x\validate\rule\dateFormat'],     'message'=>'{:preset}必须是 {0} 的日期格式'],
        'idcard'=> ['class'=>['\x\validate\rule\idCard'],             'message'=>'{:preset}必须是有效的身份证格式'],
        'licenseplate'=> ['class'=>['\x\validate\rule\licensePlate'], 'message'=>'{:preset}必须是国内有效的车牌号'],
        'macaddr'=> ['class'=>['\x\validate\rule\macAddr'],           'message'=>'{:preset}必须是有效的MAC地址'],
        'zip'=> ['class'=>['\x\validate\rule\zips'],                  'message'=>'{:preset}必须是有效的邮政编码'],
        'in'=> ['class'=>['\x\validate\rule\Ins'],                    'message'=>'{:preset}必须是在 {0} 范围内'],
        'notin'=> ['class'=>['\x\validate\rule\NotIn'],               'message'=>'{:preset}必须是不在 {0} 范围内'],
        'between' => ['class'=>['\x\validate\rule\between'],          'message'=>'{:preset}必须在 {0} - {1} 之间'],
        'notbetween' => ['class'=>['\x\validate\rule\notBetween'],    'message'=>'{:preset}必须不在 {0} - {1} 之间'],
        'max' => ['class'=>['\x\validate\rule\maxs'],                 'message'=>'{:preset}最大长度不能超过{0}个字符'],
        'min' => ['class'=>['\x\validate\rule\mins'],                 'message'=>'{:preset}最小长度不能小于{0}个字符'],
        'after' => ['class'=>['\x\validate\rule\afters'],             'message'=>'{:preset}必须是在 {0} 之后'],
        'before' => ['class'=>['\x\validate\rule\befores'],           'message'=>'{:preset}必须是在 {0} 之前'],
        'expire' => ['class'=>['\x\validate\rule\expire'],            'message'=>'{:preset}必须是在 {0} 和 {1} 之间'],
        'confirm' => ['class'=>['\x\validate\rule\confirm'],          'message'=>'{:preset}必须和 {0} 相同'],
        'different' => ['class'=>['\x\validate\rule\different'],      'message'=>'{:preset}必须和 {0} 不同'],
        'eq' => ['class'=>['\x\validate\rule\eq'],                    'message'=>'{:preset}必须 等于 {0}'],
        'neq' => ['class'=>['\x\validate\rule\neq'],                  'message'=>'{:preset}必须 不等于 {0}'],
        'egt' => ['class'=>['\x\validate\rule\egt'],                  'message'=>'{:preset}必须 大于等于 {0}'],
        'gt' => ['class'=>['\x\validate\rule\gt'],                    'message'=>'{:preset}必须 大于 {0}'],
        'elt' => ['class'=>['\x\validate\rule\elt'],                  'message'=>'{:preset}必须 小于等于 {0}'],
        'lt' => ['class'=>['\x\validate\rule\lt'],                    'message'=>'{:preset}必须 小于 {0}'],
        'username' => ['class'=>['\x\validate\rule\username'],        'message'=>'{:preset}必须是字母开头，允许 {0}-{1}位，支持字母数字下划线组合'],
        'password' => ['class'=>['\x\validate\rule\password'],        'message'=>'{:preset}必须是包含字母数字，允许 {0}-{1}位'],
        'longlat' => ['class'=>['\x\validate\rule\longlat'],          'message'=>'{:preset}必须是有效的经纬度格式'],
        'get' => ['class'=>['\x\validate\rule\get'],                  'message'=>'必须是GET请求'],
        'post' => ['class'=>['\x\validate\rule\post'],                'message'=>'必须是POST请求'],
        'ajax' => ['class'=>['\x\validate\rule\ajax'],                'message'=>'必须是AJAX请求'],
        'jwt' => ['class'=>['\x\validate\rule\jwt'],                  'message'=>'Jwt校验不通过'],
        'csrf' => ['class'=>['\x\validate\rule\csrf'],                'message'=>'Csrf校验不通过'],
    ];
    
    /**
     * 绑定
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $name
     * @param mixed $function
     * @param string $message 
     * @return void
    */
    public static function listen($name, $function, $message=null) {
        $name = strtolower($name);
        if (isset(self::$list[$name])) {
            throw new \Exception("VaildateEvent：{$name} has been registered！");
            return false;
        }
        if (is_string($function)) {
            $function = [$function];
        }
        self::$list[$name]['class'] = $function;
        self::$list[$name]['message'] = $message;

        return true;
    }

    /**
     * 触发
     * @todo 无
     * @author 小黄牛
     * @version v2.5.6 + 2021-09-15
     * @deprecated 暂不启用
     * @global 无
     * @param string $name
     * @param mixed $param
     * @return void
    */
    public static function trigger($name, $param) {
        $name = strtolower($name);
        if (isset(self::$list[$name]) == false) {
            throw new \Exception("VaildateEvent：{$name} does not exist！");
            return false;
        }
        $function = self::$list[$name];

        // 闭包
        if (is_callable($function['class'])) {
            $res = call_user_func_array($function['class'], $param);
        } else {
            $res = call_user_func_array([$function['class'][0], $function['class'][1] ?? 'run'], $param);
        }

        return [
            'status' => $res,
            'message' => $function['message'],
        ];
    }
}