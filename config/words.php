<?php
/**
 * +----------------------------------------------------------------------
 * 敏感词/分词配置
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

return [
    // +----------------------------------------------------------------------
    // | 敏感词配置
    // +----------------------------------------------------------------------
    
    // 敏感词文件列表，需要使用ROOT_APTH常量
    'sensitive_file_list' => [
        [
            'path' => ROOT_PATH.'public/word/1.txt', // 词库文件
            'char' => false, // 敏感词分隔符，false表示换行分隔
        ],
    ],
];