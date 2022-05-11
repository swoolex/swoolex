<?php
/**
 * +----------------------------------------------------------------------
 * Jwt校验
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace x\validate\rule;

class jwt
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
        $type = 'HEADER';
        
        $name = \x\Config::get('jwt.jwt_form_name');

        if ($rule) {
            $rule = explode(',', $rule);
            [$type, $name] = $rule;
            $type = trim(strtoupper($type));
            $name = trim($name);
            switch ($type) {
                case 'GET':
                    $param = \x\Request::get();
                break;
                case 'POST':
                    $param = \x\Request::post();
                break;
                case 'RAW':
                    $param = \x\Request::raw();
                break;
                case 'HEAD':
                    $param = \x\Request::header();
                break;
                default:
                    return false;
                break;
            }
        }
        if (empty($param[$name])) return false;
        
        $Jwt = new \x\Jwt();
        if (!$Jwt->is_token($param[$name])) return false;

        return true;
    }
}