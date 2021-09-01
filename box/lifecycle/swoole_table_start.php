<?php
/**
 * +----------------------------------------------------------------------
 * 当Swoole/Table内存表初始化完成时载入的事件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\lifecycle;

class swoole_table_start
{
    /**
     * 接受回调处理
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $table 表名
     * @param array $field 字段列表
     * @param bool $status 状态 true.创建成功 false.创建失败
     * @return bool
    */
    public function run($table, $field, $status) {
        // 当服务重启时，可以在这里，进行一些内存表的数据初始化渲染操作
        
        return true;
    }
}