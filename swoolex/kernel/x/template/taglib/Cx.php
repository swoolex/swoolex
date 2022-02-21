<?php
/**
 * +----------------------------------------------------------------------
 * Cx标签库
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/top-think/think-view
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\template\taglib;
use x\template\TagLib;

class Cx extends Taglib {
    /**
     * 标签定义
    */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'php'        => ['attr' => ''],
        'swlist'     => ['attr' => 'name,id,offset,length,key,mod', 'alias' => 'iterate'],
        'foreach'    => ['attr' => 'name,id,item,key,offset,length,mod', 'expression' => true],
        'if'         => ['attr' => 'condition', 'expression' => true],
        'elseif'     => ['attr' => 'condition', 'close' => 0, 'expression' => true],
        'else'       => ['attr' => '', 'close' => 0],
        'switch'     => ['attr' => 'name', 'expression' => true],
        'case'       => ['attr' => 'value,break', 'expression' => true],
        'default'    => ['attr' => '', 'close' => 0],
        'compare'    => ['attr' => 'name,value,type', 'alias' => ['eq,equal,notequal,neq,gt,lt,egt,elt,heq,nheq', 'type']],
        'for'        => ['attr' => 'start,end,name,comparison,step'],
    ];
 
    /**
     * php标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagPhp($tag, $content) {
        $parseStr = '<?php ' . $content . ' ?>';
        return $parseStr;
    }

    /**
     * foreach标签解析 循环输出数据集
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string|void
    */
    public function tagForeach($tag, $content) {
        // 直接使用表达式
        if (!empty($tag['expression'])) {
            $expression = ltrim(rtrim($tag['expression'], ')'), '(');
            $expression = $this->autoBuildVar($expression);
            $parseStr   = '<?php foreach(' . $expression . '): ?>';
            $parseStr .= $content;
            $parseStr .= '<?php endforeach; ?>';
            return $parseStr;
        }

        $name   = $tag['name'];
        $key    = !empty($tag['key']) ? $tag['key'] : 'key';
        $item   = !empty($tag['id']) ? $tag['id'] : $tag['item'];
        $empty  = isset($tag['empty']) ? $tag['empty'] : '';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $length = !empty($tag['length']) && is_numeric($tag['length']) ? intval($tag['length']) : 'null';

        $parseStr = '<?php ';

        // 支持用函数传数组
        if (':' == substr($name, 0, 1)) {
            $var  = '$_' . uniqid();
            $name = $this->autoBuildVar($name);
            $parseStr .= $var . '=' . $name . '; ';
            $name = $var;
        } else {
            $name = $this->autoBuildVar($name);
        }

        $parseStr .= 'if(is_array(' . $name . ') || ' . $name . ' instanceof \think\Collection || ' . $name . ' instanceof \think\Paginator): ';

        // 设置了输出数组长度
        if (0 != $offset || 'null' != $length) {
            if (!isset($var)) {
                $var = '$_' . uniqid();
            }
            $parseStr .= $var . ' = is_array(' . $name . ') ? array_slice(' . $name . ',' . $offset . ',' . $length . ', true) : ' . $name . '->slice(' . $offset . ',' . $length . ', true); ';
        } else {
            $var = &$name;
        }

        $parseStr .= 'if( count(' . $var . ')==0 ) : echo "' . $empty . '" ;';
        $parseStr .= 'else: ';

        // 设置了索引项
        if (isset($tag['index'])) {
            $index = $tag['index'];
            $parseStr .= '$' . $index . '=0; ';
        }

        $parseStr .= 'foreach(' . $var . ' as $' . $key . '=>$' . $item . '): ';

        // 设置了索引项
        if (isset($tag['index'])) {
            $index = $tag['index'];
            if (isset($tag['mod'])) {
                $mod = (int) $tag['mod'];
                $parseStr .= '$mod = ($' . $index . ' % ' . $mod . '); ';
            }
            $parseStr .= '++$' . $index . '; ';
        }

        $parseStr .= '?>';
        // 循环体中的内容
        $parseStr .= $content;
        $parseStr .= '<?php endforeach; endif; else: echo "' . $empty . '" ;endif; ?>';

        if (!empty($parseStr)) {
            return $parseStr;
        }

        return;
    }

    /**
     * if标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagIf($tag, $content) {
        $condition = !empty($tag['expression']) ? $tag['expression'] : $tag['condition'];
        $condition = $this->parseCondition($condition);
        $parseStr  = '<?php if(' . $condition . '): ?>' . $content . '<?php endif; ?>';

        return $parseStr;
    }

    /**
     * elseif标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagElseif($tag, $content) {
        $condition = !empty($tag['expression']) ? $tag['expression'] : $tag['condition'];
        $condition = $this->parseCondition($condition);
        $parseStr  = '<?php elseif(' . $condition . '): ?>';

        return $parseStr;
    }

    /**
     * else标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @return string
    */
    public function tagElse($tag) {
        $parseStr = '<?php else: ?>';

        return $parseStr;
    }

    /**
     * switch标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param  array $tag 标签属性
     * @param  string $content 标签内容
     * @return string
    */
    public function tagSwitch($tag, $content) {
        $name     = !empty($tag['expression']) ? $tag['expression'] : $tag['name'];
        $name     = $this->autoBuildVar($name);
        $parseStr = '<?php switch(' . $name . '): ?>' . $content . '<?php endswitch; ?>';

        return $parseStr;
    }

    /**
     * case标签解析 需要配合switch才有效
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagCase($tag, $content) {
        $value = isset($tag['expression']) ? $tag['expression'] : $tag['value'];
        $flag  = substr($value, 0, 1);

        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($value);
            $value = 'case ' . $value . ':';
        } elseif (strpos($value, '|')) {
            $values = explode('|', $value);
            $value  = '';
            foreach ($values as $val) {
                $value .= 'case "' . addslashes($val) . '":';
            }
        } else {
            $value = 'case "' . $value . '":';
        }

        $parseStr = '<?php ' . $value . ' ?>' . $content;
        $isBreak  = isset($tag['break']) ? $tag['break'] : '';

        if ('' == $isBreak || $isBreak) {
            $parseStr .= '<?php break; ?>';
        }

        return $parseStr;
    }

    /**
     * default标签解析 需要配合switch才有效
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagDefault($tag) {
        $parseStr = '<?php default: ?>';

        return $parseStr;
    }

    /**
     * compare标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param  array $tag 标签属性
     * @param  string $content 标签内容
     * @return string
    */
    public function tagCompare($tag, $content) {
        $name  = $tag['name'];
        $value = $tag['value'];
        $type  = isset($tag['type']) ? $tag['type'] : 'eq'; // 比较类型
        $name  = $this->autoBuildVar($name);
        $flag  = substr($value, 0, 1);

        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($value);
        } else {
            $value = '\'' . $value . '\'';
        }

        switch ($type) {
            case 'equal':
                $type = 'eq';
                break;
            case 'notequal':
                $type = 'neq';
                break;
        }
        $type     = $this->parseCondition(' ' . $type . ' ');
        $parseStr = '<?php if(' . $name . ' ' . $type . ' ' . $value . '): ?>' . $content . '<?php endif; ?>';

        return $parseStr;
    }

    /**
     * for标签解析
     * @todo 无
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @deprecated 暂不启用
     * @global 无
     * @param array $tag 标签属性
     * @param string $content 标签内容
     * @return string
    */
    public function tagFor($tag, $content) {
        //设置默认值
        $start      = 0;
        $end        = 0;
        $step       = 1;
        $comparison = 'lt';
        $name       = 'i';
        $rand       = rand(); //添加随机数，防止嵌套变量冲突

        //获取属性
        foreach ($tag as $key => $value) {
            $value = trim($value);
            $flag  = substr($value, 0, 1);
            if ('$' == $flag || ':' == $flag) {
                $value = $this->autoBuildVar($value);
            }

            switch ($key) {
                case 'start':
                    $start = $value;
                    break;
                case 'end':
                    $end = $value;
                    break;
                case 'step':
                    $step = $value;
                    break;
                case 'comparison':
                    $comparison = $value;
                    break;
                case 'name':
                    $name = $value;
                    break;
            }
        }

        $parseStr = '<?php $__FOR_START_' . $rand . '__=' . $start . ';$__FOR_END_' . $rand . '__=' . $end . ';';
        $parseStr .= 'for($' . $name . '=$__FOR_START_' . $rand . '__;' . $this->parseCondition('$' . $name . ' ' . $comparison . ' $__FOR_END_' . $rand . '__') . ';$' . $name . '+=' . $step . '){ ?>';
        $parseStr .= $content;
        $parseStr .= '<?php } ?>';

        return $parseStr;
    }

}