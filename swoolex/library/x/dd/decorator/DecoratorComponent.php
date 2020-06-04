<?php
// +----------------------------------------------------------------------
// | 标签基类
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace x\dd\decorator;

use x\dd\render\AbstractDump;

abstract class DecoratorComponent
{
    /**
     * @var string
     */
    protected $_head;

    /**
     * @var string
     */
    protected $_tail;

    /**
     * @var array
     */
    public $classList = [];

    /**
     * @var AbstractDump
     */
    public $value;

    /**
     * DecoratorComponent constructor.
     * @param $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * 初始化css
     */
    public function initStyle()
    {
        $config = require_once __DIR__ . '/../conf/css.php';
        // 防止重复加载
        if ($config === true) return;
        $styleStr = "<style>*{margin:0;padding:0}";
        if (!empty($config)) {
            foreach ($config as $k => $v) {
                $styleStr .= $k . "{" . $v . "}";
            }
            $styleStr .= "</style>";
        }
        return $styleStr;
    }

    /**
     * 添加样式
     * @param $className
     * @return $this
     */
    public function addClass($className)
    {
        array_push($this->classList, $className);
        return $this;
    }

    /**
     * 清除已经存在的样式列表
     * @return $this
     */
    public function clearClass()
    {
        $this->classList = [];
        return $this;
    }

    /**
     * 添加css并转发给wrap
     * @return mixed
     */
    public function addDecorator()
    {
        // 设置tag
        $parts = explode("\\", static::class);
        $class = strtolower(array_pop($parts));
        // 设置class
        $classStr = implode(' ', $this->classList);
        $this->_head = "<" . $class . " class='$classStr'>";
        $this->_tail = "</" . $class . ">";
        // 清空样式
        $this->clearClass();
        return static::wrap(func_get_args());
    }

    /**
     * 用引号包裹
     * @param $value
     * @return string
     */
    public function withQuota($value)
    {
        return '"' . $value . '"';
    }

    /**
     * 用中括号包裹
     * @param $value
     */
    public function withBracket($value)
    {
        // todo 这里后面看看是否需要单独提炼出来
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    /**
     * 没有包裹的标签
     * @param $value
     * @return string
     */
    public function noWrap($value)
    {
        return $this->_head . $value . $this->_tail;
    }

    /**
     * @return mixed
     */
    abstract public function display();
}