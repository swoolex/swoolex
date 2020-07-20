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
// 引入抽象类
use x\db\AbstractPool;

class MysqlPool extends AbstractPool {

    /**
     * 初始换最小数量连接池
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
        # 读
        for ($i=0; $i<$this->read_min; $i++) {
            # 创建 - 这是基类的方法
            $obj = $this->createRead($i);
            $this->read_count++;
            # 写入消息队列-支持协程
            $this->read_connections->push($obj);
        }
        # 写
        for ($i=0; $i<$this->write_min; $i++) {
            # 创建 - 这是基类的方法
            $obj = $this->createWrite($i);
            $this->write_count++;
            # 写入消息队列-支持协程
            $this->write_connections->push($obj);
        }
        # 日志
        for ($i=0; $i<$this->log_min; $i++) {
            # 创建 - 这是基类的方法
            $obj = $this->createLog($i);
            $this->log_count++;
            # 写入消息队列-支持协程
            $this->log_connections->push($obj);
        }
        return $this;
    }

    /**
     * 读-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $timeOut 出队最大等待时间
     * @return obj
    */
    public function read_pop($timeOut = 3) {
        $obj = null;
        if ($this->read_connections->isEmpty()) {
            // 连接数没达到最大，新建连接入池
            if ($this->read_count < $this->read_max) {
                $this->read_count++;
                $obj = $this->createRead($this->read_count);
            } else {
                // timeout为出队的最大的等待时间
                $obj = $this->read_connections->pop($timeOut);
            }
        } else {
            $obj = $this->read_connections->pop($timeOut);
        }
        return $obj;
    }

    /**
     * 读-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function read_free($obj) {
        if ($obj) {
            $obj['last_used_time'] = time();
            $this->read_connections->push($obj);
        }
    }
    
    /**
     * 写-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $timeOut 出队最大等待时间
     * @return obj
    */
    public function write_pop($timeOut = 3) {
        $obj = null;
        if ($this->write_connections->isEmpty()) {
            // 连接数没达到最大，新建连接入池
            if ($this->write_count < $this->write_max) {
                $this->write_count++;
                $obj = $this->createWrite($this->write_count);
            } else {
                // timeout为出队的最大的等待时间
                $obj = $this->write_connections->pop($timeOut);
            }
        } else {
            $obj = $this->write_connections->pop($timeOut);
        }
        return $obj;
    }
    /**
     * 写-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function write_free($obj) {
        if ($obj) {
            $obj['last_used_time'] = time();
            $this->write_connections->push($obj);
        }
    }

    
    /**
     * 日志-获取一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param int $timeOut 出队最大等待时间
     * @return obj
    */
    public function log_pop($timeOut = 3) {
        $obj = null;
        if ($this->log_connections->isEmpty()) {
            // 连接数没达到最大，新建连接入池
            if ($this->log_count < $this->log_max) {
                $this->log_count++;
                $obj = $this->createLog($this->log_count);
            } else {
                // timeout为出队的最大的等待时间
                $obj = $this->log_connections->pop($timeOut);
            }
        } else {
            $obj = $this->log_connections->pop($timeOut);
        }
        return $obj;
    }
    /**
     * 日志-归还一个连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param obj $obj 数据库连接实例
     * @return void
    */
    public function log_free($obj) {
        if ($obj) {
            $obj['last_used_time'] = time();
            $this->log_connections->push($obj);
        }
    }

    /**
     * 定时回收空闲连接
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function timing_recovery($time, $workerId) {
        // 大约10分钟检测一次连接
        \Swoole\Timer::tick($time * 1000, function () {
            # 先检测读 - 连接
            $list = [];
            # 一半最大进程为界限
            if ($this->read_connections->length() < intval($this->read_max * 0.5)) {
                echo "READ 连接池小于峰值的一半，暂不需要回收空闲连接\n";
            }
            # 堵塞循环
            while (true) {
                if (!$this->read_connections->isEmpty()) {
                    $obj = $this->read_connections->pop(0.001);
                    # 拿出最近一次使用时间
                    $last_used_time = $obj['last_used_time'];
                    # 判断回收超期时间
                    if ($this->read_count > $this->read_min && (time() - $last_used_time > $this->read_spare_time)) {
                        $this->read_count--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->read_connections->push($item);
            }
            unset($list);

            # 再检测写 - 连接
            $list = [];
            # 一半最大进程为界限
            if ($this->write_connections->length() < intval($this->write_max * 0.5)) {
                echo "WRITE 连接池小于峰值的一半，暂不需要回收空闲连接\n";
            }
            # 堵塞循环
            while (true) {
                if (!$this->write_connections->isEmpty()) {
                    $obj = $this->write_connections->pop(0.001);
                    # 拿出最近一次使用时间
                    $last_used_time = $obj['last_used_time'];
                    # 判断回收超期时间
                    if ($this->write_count > $this->write_min && (time() - $last_used_time > $this->write_spare_time)) {
                        $this->write_count--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->write_connections->push($item);
            }
            unset($list);

            
            # 再检测日志 - 连接
            $list = [];
            # 一半最大进程为界限
            if ($this->log_connections->length() < intval($this->log_max * 0.5)) {
                echo "LOG 连接池小于峰值的一半，暂不需要回收空闲连接\n";
            }
            # 堵塞循环
            while (true) {
                if (!$this->log_connections->isEmpty()) {
                    $obj = $this->log_connections->pop(0.001);
                    # 拿出最近一次使用时间
                    $last_used_time = $obj['last_used_time'];
                    # 判断回收超期时间
                    if ($this->log_count > $this->log_min && (time() - $last_used_time > $this->log_spare_time)) {
                        $this->log_count--;
                    } else {
                        array_push($list, $obj);
                    }
                } else {
                    break;
                }
            }
            foreach ($list as $item) {
                $this->log_connections->push($item);
            }
            unset($list);
        });
        
        // 5秒更新一次当前数据库连接数
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
    
    /**
     * 清空连接池
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function clean() {
        # 读 - 连接
        while (true) {
            if (!$this->read_connections->isEmpty()) {
                $obj = $this->read_connections->pop(0.001);
                $this->read_count--;
            } else {
                break;
            }
        }
        # 写 - 连接
        while (true) {
            if (!$this->write_connections->isEmpty()) {
                $obj = $this->write_connections->pop(0.001);
                $this->write_count--;
            } else {
                break;
            }
        }
        # 日志 - 连接
        while (true) {
            if (!$this->log_connections->isEmpty()) {
                $obj = $this->log_connections->pop(0.001);
                $this->log_count--;
            } else {
                break;
            }
        }
    }

    /**
     * 创建数据库连接实例
     * @todo 无
     * @author 小黄牛
     * @version v1.0.12 + 2020.04.29
     * @deprecated 暂不启用
     * @global 无
     * @param array $database 数据库连接配置
     * @return swoole_mysql
    */
    protected function createDb($database) {
        // 协程数据库连接支持
        $db = new \Swoole\Coroutine\Mysql();
        $db->connect($database);
        return $db;
    }
}