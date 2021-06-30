<?php
/**
 * +----------------------------------------------------------------------
 * 模板引擎配置
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/


return [
    // 模板引擎类型 仅支持 SwooleX 支持扩展
    'type'         => 'SwooleX',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'    => 1,
    // 模板路径
    'view_path'    => ROOT_PATH . '/app/view',
    // 模板后缀
    'view_suffix'  => 'html',
    // 模板文件名分隔符
    'view_depr'    => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'    => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'      => '}',
    // 标签库标签开始标记
    'taglib_begin' => '{',
    // 标签库标签结束标记
    'taglib_end'   => '}',
    // 布局模板开关
    'layout_on'    => false,
    // 布局模板入口文件
    'layout_name'  => 'layout',
    // 布局模板的内容替换标识
    'layout_item'  => '{__CONTENT__}',
    // 分页配置参数
    'paginate'     => [
        // 分页样式类
        'type'     => '\x\page\Bootstrap',
        // 分页变量参数
        'var_page' => 'page',
        // 分页URL参数
        'query'    => [],
        // 分页锚点参数
        'fragment' => '',
    ],
    // HTTP控制器fetch时默认的响应headers
    'http_response_headers' => [
        'Content-type' => 'text/html; charset=utf-8'
    ],
];
