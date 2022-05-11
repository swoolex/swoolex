<?php
namespace x\memcached;
use design\AbstractMemcachedPool;

/**
 * Created by PhpStorm.
 * User: f
 * Date: 2021/11/4
 * Time: 14:45
 */
class Pool extends AbstractMemcachedPool
{
    /**
     * 启动连接池
     * @author 小黄牛
     * @version v1.2.4 + 2020.07.20
     * @return $this|null
     */
    public function init() {

        if ($this->config['pool_num'] <= 0) return false;

        $start_time = explode(' ',microtime());

        $this->min = ($this->config['pool_num']>1) ? ceil($this->config['pool_num']/2) : 1;
        $this->max = $this->config['pool_num'];

        for ($i=0; $i<$this->min; $i++) {
            $this->count++;
            $this->connections[] = $this->create();
        }
        \design\StartRecord::memcached_reload($start_time);

        return $this;
    }

    /**
     * 获取一个连接
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @return obj
     */
    public function pop() {
        // 队列被取空
        if (!$this->connections) {
            // 连接数没达到最大，新建连接入池
            if ($this->count < $this->max) {
                $this->count++;
                $obj = $this->create();
            }
        } else {
            $obj = array_pop($this->connections);
        }
        return $obj['db'];
    }

    /**
     * 归还一个连接
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @param obj $db 连接实例
     * @return bool
     */
    public function free($db) {
        if ($db) {
            $obj = [
                'last_used_time' => time(),
                'db' => $db,
            ];
            return $this->connections[] = $obj;
        }
        return false;
    }

    /**
     * 定时统计连接
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     */
    public function timing_recovery() {
        // 5秒更新一次当前数据库连接数
        if ($this->config['is_monitor'] && $this->config['pool_num'] > 0) {
            $time = $this->config['monitor_time']*1000;
            \Swoole\Timer::tick($time, function () {
                // 10分钟没用就回收
                $spare_time = $this->config['spare_time'];

                $list = [];
                # 一半最大进程为界限
                if (count($this->connections) < intval($this->max * 0.5)) {
                    return false;
                }
                # 堵塞循环
                while (true) {
                    if ($this->connections) {
                        $obj = array_pop($this->connections);
                        # 拿出最近一次使用时间
                        $last_used_time = $obj['last_used_time'];
                        # 判断回收超期时间
                        if ($this->count > $this->min && (time() - $last_used_time > $spare_time)) {
                            $this->count--;
                        } else {
                            array_push($list, $obj);
                        }
                    } else {
                        break;
                    }
                }
                $num = count($list);
                foreach ($list as $item) {
                    $this->connections[] = $item;
                }

                $path = BOX_PATH.'env'.DS.'memcache_pool_num.count';
                $json = \Swoole\Coroutine\System::readFile($path);
                $array = [];
                if ($json) {
                    $array = json_decode($json, true);
                }
                $array[0] = $num;
                \Swoole\Coroutine\System::writeFile($path, json_encode($array));
                unset($list);
                unset($json);
                unset($array);
                unset($path);
            });

            \design\StartRecord::mongodb_monitor();
        }
    }

    /**
     * 创建连接实例
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.29
     * @return false|array
     */
    protected function create()
    {
        try {
            $db = new \Memcache();
            //集群模式
            if(count($this->config['host'])>1){

                foreach ($this->config['host'] as $v)
                {
                    list($host,$port) = explode(":",$v);
                    $db->addServer($host,$port);
                }

            }else{  //单机模式
                list($host,$port) = explode(":",$this->config['host']);
                $db->connect($host,$port,$this->config['time_out']);
            }
        } catch(\Exception $e){
            throw new \Exception("new Memcache Error ".$e->getMessage());
            return false;
        }
        return [
            'last_used_time' => time(),
            'db' => $db,
        ];
    }
}