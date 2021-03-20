<?php
// +----------------------------------------------------------------------
// | Mysql连接池
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\db;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
// 引入抽象类
use x\db\AbstractPool;

class MysqlPool extends AbstractPool {

    /**
     * 启动连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.2.4 + 2020.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $
     * @param mixed $
     * @return $this|null
    */
    public function init() {
        foreach ($this->config as $key=>$value) {
            $this->config[$key]['connections'] = $this->createDb($value, $value['pool_num']);
        }
        return $this;
    }

    /**
     * 获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return obj
    */
    public function pop($key) {
        if (!isset($this->config[$key])) return false;

        if ($this->config[$key]['pool_num'] <= 0) {
            $this->pop_error($key);
            throw new \Exception("Dao ".$key." Pop <= 0");
            return false;
        }
        $this->config[$key]['pool_num']--;
        if (!$this->config[$key]['connections']) return false;
        
        $pool = $this->config[$key]['connections']->get();

        $res = $pool->getAttribute(\PDO::ATTR_SERVER_INFO);
        if ($res === false) {
            $this->free($key, null);
            return $this->pop($key);
        }

        return $pool;
    }

    /**
     * 归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function free($key, $obj) {
        $this->config[$key]['pool_num']++;
        if (!$this->config[$key]['connections']) return false;

        return $this->config[$key]['connections']->put($obj);
    }
    
    /**
     * 定时统计连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function timing_recovery($workerId) {
        // 5秒更新一次当前数据库连接数
        if (\x\Config::get('mysql.is_monitor')) {
            \Swoole\Timer::tick(5000, function () use($workerId) {
                $path = ROOT_PATH.'/other/env/mysql_pool_num.count';
                $json = \Swoole\Coroutine\System::readFile($path);
                $array = [];
                if ($json) {
                    $array = json_decode($json, true);
                }
                $num = 0;
                foreach ($this->config as $key=>$value) {
                    $num += $value['pool_num'];
                }
                $array[$workerId] = $num;
                \Swoole\Coroutine\System::writeFile($path, json_encode($array));
                unset($json);
                unset($array);
                unset($path);
            });
        }
    }
    
    /**
     * 清空连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function clean() {
        foreach ($this->config as $key=>$value) {
            $value['connections']->close();
            $this->config[$key]['pool_num'] = 0;
        }
    }

    /**
     * 创建数据库连接实例
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param array $database 数据库连接配置
     * @param int $szie 连接池长度
     * @return swoole_mysql
    */
    protected function createDb($database, $size) {
        // 协程数据库连接支持
        return new PDOPool((new PDOConfig)
            ->withHost($database['host'])
            ->withPort($database['port'])
            ->withDbName($database['database'])
            ->withCharset($database['charset'])
            ->withUsername($database['user'])
            ->withPassword($database['password'])
        , $size);
    }

    /**
     * 当连接池数小于等于0时，回调的通知函数
     * @todo 无
     * @author 小黄牛
     * @version v1.1.5 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 连接池类型
     * @return void
    */
    protected function pop_error($type) {
        $obj = new \other\lifecycle\mysql_pop_error();
        $obj->run($type);
        return false;
    }
}