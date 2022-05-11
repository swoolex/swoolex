<?php
/**
 * +----------------------------------------------------------------------
 * Mysql连接池
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db\mysql;
use Swoole\Database\PDOConfig;
use Swoole\Database\PDOPool;
use design\AbstractMysqlPool;

class Pool extends AbstractMysqlPool {

    /**
     * 启动连接池
     * @author 小黄牛
     * @version v1.2.4 + 2020.07.20
     * @return $this|null
    */
    public function init() {
        $start_time = explode(' ',microtime());

        foreach ($this->config as $key=>$value) {
            $this->config[$key]['connections'] = $this->createDb($value, $value['pool_num']);
        }
        
        \design\StartRecord::mysql_reload($start_time);
        
        return $this;
    }

    /**
     * 获取一个连接
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @return obj
    */
    public function pop($key) {
        if (!isset($this->config[$key])) return false;

        if ($this->config[$key]['pool_num'] <= 0) {
            // 生命周期通知
            \design\Lifecycle::mysql_pop_error($key);

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
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @param obj $obj 数据库连接实例
     * @return bool
    */
    public function free($key, $obj) {
        $this->config[$key]['pool_num']++;
        if (!$this->config[$key]['connections']) return false;

        return $this->config[$key]['connections']->put($obj);
    }
    
    /**
     * 定时统计连接
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
    */
    public function timing_recovery() {
        // 5秒更新一次当前数据库连接数
        if (\x\Config::get('mysql.is_monitor')) {
            \Swoole\Timer::tick(5000, function () {
                $path = BOX_PATH.'env'.DS.'mysql_pool_num.count';
                $json = \Swoole\Coroutine\System::readFile($path);
                $array = [];
                if ($json) {
                    $array = json_decode($json, true);
                }
                $num = 0;
                foreach ($this->config as $key=>$value) {
                    $num += $value['pool_num'];
                }
                $array[0] = $num;
                \Swoole\Coroutine\System::writeFile($path, json_encode($array));
                unset($json);
                unset($array);
                unset($path);
            });
            
            \design\StartRecord::mysql_monitor();
        }
    }
    
    /**
     * 清空连接池
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
    */
    public function clean() {
        foreach ($this->config as $key=>$value) {
            $value['connections']->close();
            $this->config[$key]['pool_num'] = 0;
        }
    }

    /**
     * 创建数据库连接实例
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @param array $database 数据库连接配置
     * @param int $szie 连接池长度
     * @return swoole_mysql
    */
    protected function createDb($database, $size) {
        // 协程数据库连接支持
        return new PDOPool((new PDOConfig)
            ->withDriver($database['driver'])
            ->withHost($database['host'])
            ->withPort($database['port'])
            ->withDbName($database['database'])
            ->withCharset($database['charset'])
            ->withUsername($database['user'])
            ->withPassword($database['password'])
        , $size);
    }

}