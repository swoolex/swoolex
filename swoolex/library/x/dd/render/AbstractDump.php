<?php
// +----------------------------------------------------------------------
// | dump基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\dd\render;

abstract class AbstractDump
{
    /**
     * @var
     */
    public $value;

    /**
     * DumpString constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
        $this->init();
    }

    /**
     * 初始化一些需要展示的内容
     */
    protected function init()
    {
        $config = require __DIR__ . '/../conf/decorator.php';
        if ($config === true) return;

        if (!empty($config)) {
            foreach ($config as $key => $value) {
                if (!array_key_exists('value', $value)) continue;
                if (!array_key_exists('type', $value)) $value['type'] = 'span';
                if (!array_key_exists('style', $value)) {
                    $value['style'] = [];
                } elseif (!is_array($value['style'])) {
                    $value['style'] = [];
                }
                if (!array_key_exists('params', $value)) {
                    $value['params'] = [];
                } elseif (!is_array($value['params'])) {
                    $value['params'] = [];
                }
                // invoke
                $this->$key = $this->returnValue($value['value'], $value['type'], $value['style'], $value['params']);
            }
        }
    }

    /**
     * @var array
     */
    protected $_decorator = [];

    /**
     * @param $type
     * @param array $classArr
     * @param array $params
     * @param string $value
     * @return mixed
     */
    public function returnValue($value = '', $type = 'span', $classArr = ['nine-span'], $params = ['withQuota' => true])
    {
        if (is_array($value) && is_object($value) == false) {
            return implode('', $value);
        }
        return ($this->returnDecorator($type, $classArr, $value, $params))->value;
    }

    /**
     * @param $type
     * @param array $classArr
     * @param array $params
     * @param string $value
     * @return mixed
     */
    protected function returnDecorator($type, $classArr = [], $value = '', $params = [])
    {
        $decorator = $this->{$type}($value ?: $this->value);
        if (!empty($classArr)) {
            foreach ($classArr as $class) {
                $decorator->addClass($class);
            }
        }
        // 因为是单例，所以这里的value需要重新修改一下
        $decorator->value = $value;
        return $decorator->addDecorator($params);
    }

    /**
     * @param $value
     * @param null $type
     * @param array $classArr
     * @param array $params
     */
    public function display($value = '', $type = null, $classArr = [], $params = [])
    {
        if (is_array($value)) {
            $value = implode('', $value);
        } elseif ($value == '') {
            $value = $this->value;
        }

        // 此时为空代表已经拼接好
        // 拼接一个div包裹起来
        if (is_null($type)) {
            $divDecorator = $this->returnDecorator('div', ['nine-div'], $value);
        } else {
            $typeDecorator = $this->returnDecorator($type, $classArr, $value, $params);

            $divDecorator = $this->returnDecorator('div', ['nine-div'], $typeDecorator->value);
        }
        return $divDecorator->display();
    }

    /**
     * @param array $arr
     * @param int $depth
     * @return array
     */
    protected function parseArr(array $arr, $depth = 1)
    {
        if (empty($arr)) {
            return [];
        }
        // 首先导入array
        $returnArr = [];
        $returnArr[] = $this->returnValue("array:" . count($arr), 'span', ['nine-span'], ['withQuota' => false]);
        // 导入一个[
        $returnArr[] = $this->_leftBracket;
        // 导入一个▶
        $returnArr[] = $this->_triangle;
        $pushValue = "";

        foreach ($arr as $key => $value) {
            // 拼接key和value
            $key = $this->returnValue($key, 'span', ['nine-span'], ['withQuota' => is_int($key) ? false : true]);
            if (is_array($value)) {
                $value = $this->returnValue(self::parseArr($value, $depth + 1));
            } else {
                $value = $this->returnValue($value);
            }
            $pushValue .= $key . $this->_needle . $value . "</br>";
        }
        // 外层包裹一个p
        $returnArr[] = $this->returnValue($pushValue, 'p', ["depth-" . $depth]);
        $devideSpan = $this->returnValue("", 'span', ["depth-" . ($depth - 1)], ['withQuota' => false]);

        $returnArr[] = $devideSpan . $this->_rightBracket;
        return $returnArr;
    }

    /**
     * 反射解析函数的参数
     * @param array $params
     * @return array
     */
    protected function parseParams(Array $params)
    {
        $renderParams = [];
        if (!empty($params)) {
            foreach ($params as $param) {
                $name = $this->returnValue($param->name, 'span', ['nine-span', 'font-15'], ['withQuota' => false]);
                if ($param->isDefaultValueAvailable()) {
                    $default = $this->_spaceOne .
                        $this->returnValue(
                            $param->getDefaultValue(),
                            'span',
                            ['nine-span', 'gray-color'],
                            ['withQuota' => false]
                        );
                } else {
                    $default = '';
                }
                $renderParams[] = $name . $default;
            }
        }
        return $renderParams;
    }

    /**
     * @param $decorator
     * @return \dd\decorator\DecoratorComponent
     */
    public function __get($decorator)
    {
        array_key_exists($decorator, $this->_decorator) ?: $this->_decorator[$decorator] = $this->withObject("x\\dd\\decorator\\" . ucfirst($decorator), $this->value);
        return $this->_decorator[$decorator];
    }

    /**
     * 为方便层层包裹
     * @param $decorator
     * @param $arguments
     * @return \dd\decorator\DecoratorComponent
     */
    public function __call($decorator, $arguments)
    {
        array_key_exists($decorator, $this->_decorator) ?: $this->_decorator[$decorator] = $this->withObject("x\\dd\\decorator\\" . ucfirst($decorator), array_pop($arguments));
        return $this->_decorator[$decorator];
    }

    /**
     * @param $class
     * @param null $arguments
     * @return mixed
     */
    protected function withObject($class, $arguments = null)
    {
        return is_null($arguments) ? new $class : new $class($arguments);
    }

    abstract public function render();
}