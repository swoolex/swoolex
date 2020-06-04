<?php
// +----------------------------------------------------------------------
// | 在这里配置需要初始化的装饰形状
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

/**
 * key 最后通过$this->key来获取
 * value 代表他最终呈现的形式
 * type 代表他的标签 默认是span
 * style 使用什么样式，在css.php中配置，可以为[]
 * params 在用到具体的type所对应的decorator时所需要的参数，这里可以为[]
 */

return [
    '_leftBracket' => [
        'value' => "[",
        'type' => 'span',
        'style' => ['nine-span', 'black-color'],
        'params' => ['withQuota' => false]
    ],
    '_rightBracket' => [
        'value' => "]",
        'type' => 'span',
        'style' => ['nine-span', 'black-color'],
        'params' => ['withQuota' => false]
    ],
    '_triangle' => [
        'value' => "▶",
        'type' => 'span',
        'style' => ['nine-span', 'gray-color', 'font-12'],
        'params' => ['withQuota' => false]
    ],
    '_needle' => [
        'value' => "=>",
        'type' => 'span',
        'style' => ['nine-span', 'black-color'],
        'params' => ['withQuota' => false]
    ],
    '_leftBraces' => [
        'value' => "{",
        'type' => 'span',
        'style' => ['nine-span', 'black-color'],
        'params' => ['withQuota' => false]
    ],
    '_rightBraces' => [
        'value' => "}",
        'type' => 'span',
        'style' => ['nine-span', 'black-color'],
        'params' => ['withQuota' => false]
    ],
    '_spaceZero' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-0'],
        'params' => ['withQuota' => false]
    ],
    '_spaceOne' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-1'],
        'params' => ['withQuota' => false]
    ],
    '_spaceTwo' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-2'],
        'params' => ['withQuota' => false]
    ],
    '_spaceThree' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-3'],
        'params' => ['withQuota' => false]
    ],
    '_spaceFour' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-4'],
        'params' => ['withQuota' => false]
    ],
    '_spaceFive' => [
        'value' => "",
        'type' => 'span',
        'style' => ['nine-span', 'depth-5'],
        'params' => ['withQuota' => false]
    ],
];