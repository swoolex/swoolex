<?php
/**
 * +----------------------------------------------------------------------
 * PHP反射读取注解
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\doc;

class Annotate
{
    
    /**
     * 启动注解解析
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.25
     * @deprecated 暂不启用
     * @global 无
     * @param string $class 对应类的命名空间
     * @return array
    */
    public static function run($class) {
        if (!class_exists($class)) {
            throw new \Exception("Doc：Class No existent~");
        }

        // 使用ReflectionClass类
        $reflection = new \ReflectionClass($class);

        // 解析注解 - 获得类的路由参数
        $controller = DocParser::getInstance()->parse($reflection->getDocComment());

        /// 获得成员函数
        $action = [];
        $methods = $reflection->getMethods();
        foreach ($methods as $method) {
            if ($method->class == $class) {
                # 获得成员函数对应的注释内容
                $action[$method->name] = DocParser::getInstance()->parse($method->getDocComment());
            }
        }
        
        return [
            'class' => $controller,
            'function' => $action
        ];
    }
}