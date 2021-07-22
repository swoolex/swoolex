<?php
/**
 * +----------------------------------------------------------------------
 * 数据库操作类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class Db {
    /**
     * 数据库驱动实例
    */
    private $DAO;
    
    /**
     * 选择连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param string $data 连接池标识，不传默认第一个标识
     * @return void
    */
    public function __construct($data=null) {
        $class = '\x\\db\\'.\x\Config::get('mysql.driver').'\\Dao';
        $this->DAO = new $class($data);
    }

    /**
     * SQL构造器注入
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function __call($name, $arguments=[]) {
        return call_user_func_array([$this->DAO, $name], $arguments);
    }
}