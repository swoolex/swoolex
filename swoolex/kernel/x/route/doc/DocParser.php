<?php
/**
 * +----------------------------------------------------------------------
 * 解析注解
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\route\doc;

class DocParser {
    private $params = [];
    /**
     * args的单引号占位符
    */
    private $placeholder = '%SSSSS%';
    /**
     * 允许的注解
    */
    private $doc = [
        'Ioc', // 容器
        'RequestMapping', // 方法路由绑定
        'AopBefore', // 前置
        'AopAfter', // 后置
        'AopAround', // 环绕
        'AopThrows', // 异常
        'Controller', // 控制器路由绑定
        'onRoute', // 不允许访问的路由
        'Param', // 参数过滤
        'TestCase', // 单元测试
        'Csrf', // Csrf
        'Jwt', // Jwt
    ];

    private static $instance = null;
    private function __construct(){}
    private function __clone(){}
 
    public static function getInstance(){
        if(self::$instance == null){
            self::$instance = new DocParser();
        }
        return self::$instance;
    }

    /**
     * 切割注解
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param string $doc 注解
     * @return array
    */
    public function parse($doc = '') {
        // 清空单例缓存
        $this->params = [];

        if ($doc == '') return $this->params;
        
        // 使用正则匹配出/***/
        if (preg_match('#^/\*\*(.*)\*/#s', $doc, $comment ) === false) return $this->params;

        // 获取注解
        $comment = trim($comment[1]);
        
        // 将注解按*号切割
        if (preg_match_all('#^\s*\*(.*)#m', $comment, $lines ) === false) return $this->params;

        // 开始解析注解
        $this->parseLines($lines[1]);

        return $this->params;
    }

    /**
     * 注解按行解析
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param array $lines 注解
     * @return void
    */
    private function parseLines($lines) {
        foreach ($lines as $line) {
            $this->parseLine($line);
        }
    }

    /**
     * 注解解析行
     * @todo 无
     * @author 小黄牛
     * @version v1.1.4 + 2020.07.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $line 每行的注解
     * @return array
    */
    private function parseLine($line) {
        // 删除左右两侧空格
        $line = trim ( $line );
        
        if (empty($line)) return false;
        
        $return = [];
        if (strpos($line, '@') === 0) {
            $string = substr($line, 1, strlen($line));

            $array = explode('(', $string);
            $param = $array[0];

            // 开放自定义注解
            if ($this->check($param) == false) {
                // 查找自定义注解类地址
                $file = BOX_PATH.'/annotation/'.$param.'.php';
                // 不存在则抛弃
                if (!file_exists($file)) {
                    return false;
                }
            }

            $string = str_replace($param.'(', '', $string);
            $value = substr($string, 0, strlen($string)-1);
            $value = preg_replace ( "/\s(?=\s)/","\\1", $value );
            $value = str_replace('" ,', '",', $value);
            
            $value_list = explode('",', $value);

            foreach ($value_list as $v) {
                $length = strpos($v, '=');
                $key = trim(substr($v, 0, $length));
                $val = str_replace('"', '', trim(substr($v, $length+1, strlen($v))));

                # 参数要特殊处理
                if ($key == 'args') {
                    $val = $this->parseArgs($val);
                }

                $return[$key] = $val;
            }
            # 以下内置注解不需要特殊存储
            $arr = [
                'RequestMapping',
                'AopBefore',
                'AopAfter',
                'AopAround',
                'AopThrows',
                'Controller',
                'onRoute',
            ];
            if (in_array($param, $arr)) {
                $this->params[$param] = $return;
            } else {
                $index = 0;
                if (isset($this->params[$param])) $index = count($this->params[$param]);
                
                $this->params[$param][$index] = $return;
            }
            return true;
        }
        
        return false;
    }

    /**
     * 特殊处理传递的参数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.12
     * @deprecated 暂不启用
     * @global 无
     * @param string $val 参数
     * @return array
    */
    private function parseArgs($val) {
        # 提取单引号里的内容
        preg_match_all('/([\'"])([^\'"\.]*?)\1/',$val, $match);
        $list = $match[2];
        foreach ($list as $v) {
            # 加上单引号
            $v = "'".$v."'";
            # 植入占位符
            $string = str_replace(",", $this->placeholder, $v);
            # 替换内容
            $val = str_replace($v, $string, $val);
        }
        # 切割参数
        $param = explode(',', $val);
        # 把占位符取消掉
        foreach ($param as $k=>$v) {
            $param[$k] = str_replace($this->placeholder, ",", $v);
        }

        return $param;
    }

    /**
     * 验证是否允许解析的注解
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param string $keys
     * @return bool
    */
    private function check($keys) {
        foreach ($this->doc as $v) {
            if ($keys == $v) return true;
        }

        return false;
    }
}