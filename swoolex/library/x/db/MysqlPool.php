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
        # 读 - 连接
        $this->read_connections = $this->createDb($this->read_database, $this->read);
        $this->read_count = $this->read;
        # 写 - 连接
        $this->write_connections = $this->createDb($this->write_database, $this->write);
        $this->write_count = $this->write;
        # 日志 - 连接
        $this->log_connections = $this->createDb($this->log_database, $this->log);
        $this->log_count = $this->log;
        return $this;
    }

    /**
     * 读-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return obj
    */
    public function read_pop() {
        if ($this->read_count <= 0) {
            $this->pop_error('read');
            throw new \Exception("Dao Read Pop <= 0");
            return false;
        }
        $this->read_count--;
        if (!$this->read_connections) return false;
        return $this->read_connections->get();
    }

    /**
     * 读-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function read_free($obj) {
        $this->read_count++;
        if (!$this->read_connections) return false;
        return $this->read_connections->put($obj);
    }
    
    /**
     * 写-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return obj
    */
    public function write_pop() {
        if ($this->write_count <= 0) {
            $this->pop_error('write');
            throw new \Exception("Dao Write Pop <= 0");
            return false;
        }
        $this->write_count--;
        if (!$this->write_connections) return false;
        return $this->write_connections->get();
    }

    /**
     * 写-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function write_free($obj) {
        $this->write_count++;
        if (!$this->write_connections) return false;
        return $this->write_connections->put($obj);
    }

    /**
     * 日志-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @return obj
    */
    public function log_pop() {
        if ($this->log_count <= 0) {
            $this->pop_error('log');
            throw new \Exception("Dao Log Pop <= 0");
            return false;
        }
        $this->log_count--;
        if (!$this->log_connections) return false;
        return $this->log_connections->get();
    }
    /**
     * 日志-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function log_free($obj) {
        $this->log_count++;
        if (!$this->log_connections) return false;
        return $this->log_connections->put($obj);
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
        // 15分钟检测一次连接是否存活
        $outtime = 900*1000;
        \Swoole\Timer::tick($outtime, function () use($workerId) {
            # 堵塞循环
            $list = [];
            $num = 0;
            $max = ceil($this->read_count / 2);
            for ($i=0; $i<$max; $i++) {
                $obj = $this->read_connections->get();
                if ($obj) {
                    try {
                        $obj->getAttribute(\PDO::ATTR_SERVER_INFO);
                    } catch (\Exception $e) {
                        if ($e->getCode() == 'HY000') {
                            $num++;
                            continue;
                        }
                    }
                    array_push($list, $obj);
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->read_connections->put($item);
            }
            // 补充连接池
            for ($i=0; $i<$num; $i++) {
                $this->read_connections->put(null);
            }
            $this->read_count = $this->read_count-$num;
            unset($list);

            # 堵塞循环
            $list = [];
            $num = 0;
            $max = ceil($this->write_count / 2);
            for ($i=0; $i<$max; $i++) {
                $obj = $this->write_connections->get();
                if ($obj) {
                    try {
                        $obj->getAttribute(\PDO::ATTR_SERVER_INFO);
                    } catch (\Exception $e) {
                        if ($e->getCode() == 'HY000') {
                            $num++;
                            continue;
                        }
                    }
                    array_push($list, $obj);
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->write_connections->put($item);
            }
            // 补充连接池
            for ($i=0; $i<$num; $i++) {
                $this->write_connections->put(null);
            }
            $this->write_count = $this->write_count-$num;
            unset($list);
            
            # 堵塞循环
            $list = [];
            $num = 0;
            $max = ceil($this->log_count / 2);
            for ($i=0; $i<$max; $i++) {
                $obj = $this->log_connections->get();
                if ($obj) {
                    try {
                        $obj->getAttribute(\PDO::ATTR_SERVER_INFO);
                    } catch (\Exception $e) {
                        if ($e->getCode() == 'HY000') {
                            $num++;
                            continue;
                        }
                    }
                    array_push($list, $obj);
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->log_connections->put($item);
            }
            // 补充连接池
            for ($i=0; $i<$num; $i++) {
                $this->log_count->put(null);
            }
            $this->log_count = $this->log_count-$num;
            unset($list);
        });
        // 5秒更新一次当前数据库连接数
        if (\x\Config::run()->get('mysql.is_monitor')) {
            \Swoole\Timer::tick(5000, function () use($workerId) {
                $path = ROOT_PATH.'/env/mysql_pool_num.count';
                $json = \Swoole\Coroutine\System::readFile($path);
                $array = [];
                if ($json) {
                    $array = json_decode($json, true);
                }
                $array[$workerId] = $this->read_count+$this->write_count+$this->log_count;
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
        # 读 - 连接
        $this->read_count = 0;
        $this->read_connections->close();
        # 写 - 连接
        $this->write_count = 0;
        $this->write_connections->close();
        # 日志 - 连接
        $this->log_count = 0;
        $this->log_connections->close();
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
        $obj = new \lifecycle\mysql_pop_error();
        $obj->run($type);
        return false;
    }
}