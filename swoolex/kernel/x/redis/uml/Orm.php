<?php
/**
 * +----------------------------------------------------------------------
 * Redis对象建模组件
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\redis\uml;

class Orm
{
	/**
	 * Redis实例
	*/
	private $Redis = null;
    /**
     * 建模前缀
    */
    private $prefix = ':uml';
    /**
     * HASH表名
    */
    private $hash_key = ':hash';
    /**
     * GEO表名
    */
    private $geo_key = ':geo';
    /**
     * equal表名
    */
    private $equal_key = ':equal';
    /**
     * rand表名
    */
    private $rand_key = ':rand';
    /**
     * 更新回写的记录hash表名
    */
    private $timer_key = ':timer';
    /**
     * 表名
    */
    private $table;
    /**
     * 主键条件
    */
    private $id;
    /**
     * GEO条件
    */
    private $geo;
    /**
     * Like条件
    */
    private $like;
    /**
     * 普通条件
    */
    private $where;
    /**
     * 字段
    */
    private $field;
    /**
     * 记录数
    */
    private $limit;
    /**
     * 分页记录数
    */
    private $page;
    /**
     * 排序
    */
    private $order;
    /**
     * 错误原因
    */
    private $error_msg;
    
    /**
	 * 初始化连接
	 * @author 小黄牛
	 * @version v2.5.26 + 2022-05-11
	*/
    public function __construct() {
        $this->Redis = new \x\Redis($this->driver);
        $this->Redis->select($this->database);
        $array = explode('\\', strtolower(get_class($this)));
        $this->table = ':'.end($array);
        $this->hash_key = $this->prefix.$this->hash_key.$this->table.':';
        $this->equal_key = $this->prefix.$this->equal_key.$this->table.':';
        $this->rand_key = $this->prefix.$this->rand_key.$this->table.':';
        $this->geo_key = $this->prefix.$this->geo_key.$this->table;
        $this->timer_key = $this->prefix.$this->timer_key.$this->table;
        $this->clean_up();
    }
    
	/**
     * 利用析构函数，自动回收
     * @author 小黄牛
	 * @version v2.5.26 + 2022-05-11
    */
    public function __destruct() {
        $this->Redis->return();
	}
	
	/**
     * 选择ID
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string|int $id
     * @return this
    */
    public function id($id) {
        $this->id = $id;
        return $this;
    }
    
    /**
     * 打印字段
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 显示字段
     * @return this
    */
    public function field($field) {
        if (is_array($field)) {
            $this->field = $field;
        } else {
            $this->field = explode(',', str_replace(' ', '', $field));
        }
        return $this;
    }
    
    /**
     * 指定条数
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param int $left 左
     * @param int $right 右
     * @return this
    */
    public function limit($left, $right=null) {
        if (!$right) {
            $right = $left;
            $left = 0;
        }
        $this->limit = [
            'left' => $left, 
            'right' => $right
        ];
        return $this;
    }
    
    /**
     * 分页条数
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param int $left 左
     * @param int $right 右
     * @return this
    */
    public function page($left, $right) {
        $this->page = [
            'left' => $left, 
            'right' => $right
        ];
        return $this;
    }
    
    /**
     * GEO查询条件
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param float $longitude 经度
     * @param float $latitude 纬度
     * @param int $range 查询的半径范围
     * @return this
    */
    public function geo($longitude, $latitude, $range=5) {
        $this->geo[] = [
            'longitude' => $longitude, 
            'latitude' => $latitude,
            'range' => $range,
        ];
        return $this;
    }

    /**
     * Like查询条件
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 字段
     * @param string $value 条件
     * @param string $operator 表达式 %s前匹配  %s%模糊匹配 s%后匹配
     * @return this
    */
    public function like($field, $value, $operator='%s%') {
        $this->like[] = [
            'field' => $field, 
            'value' => $value,
            'operator' => strtolower(str_replace(' ', '', $operator)),
        ];
        return $this;
    }

    /**
     * 查询条件
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 可以为批量表达式，也可以是字段
     * @param string $operator 表达式
     * @param string $value 查询模式
     * @return this
    */
    public function where($field, $operator=null, $value=false) {
        if (!$field) return $this;
        // 数组
        if (is_array($field)) {
            foreach ($field as $v) {
                $field = $v[0];
                $rule = strtolower($v[1]);
                if ($rule == '=') {$rule = 'eq';}

                // 等于查询时，同个字段，只能存在一个场景，如果是in传递数组
                if (isset($this->where[$field]) && $rule == 'eq') {
                    foreach ($this->where[$field] as $k => $val) {
                        if ($val['rule'] == $v[1]) {
                            $this->where[$field][$k] = [
                                'rule' => $v[1],
                                'value' => $v[2],
                            ];
                            return $this;
                        }
                    }
                }
                $this->where[$field][] = [
                    'rule' => $v[1],
                    'value' => $v[2],
                ];
            }
        // 键值对
        } else {
            if ($value !== false) {
                $rule = strtolower($operator);
                if ($rule == '=') {$rule = 'eq';}

                // 等于查询时，同个字段，只能存在一个场景，如果是in传递数组
                if (isset($this->where[$field]) && $rule == 'eq') {
                    foreach ($this->where[$field] as $k => $val) {
                        if ($val['rule'] == $operator) {
                            $this->where[$field][$k] = [
                                'rule' => $operator,
                                'value' => $value,
                            ];
                            return $this;
                        }
                    }
                }
                $this->where[$field][] = [
                    'rule' => $operator,
                    'value' => $value,
                ];
            } else {
                if (isset($this->where[$field])) {
                    foreach ($this->where[$field] as $k => $val) {
                        if ($val['rule'] == '=' || $val['rule'] == 'eq') {
                            $this->where[$field][$k] = [
                                'rule' => 'eq',
                                'value' => $operator,
                            ];
                            return $this;
                        }
                    }
                }
                $this->where[$field][] = [
                    'rule' => 'eq',
                    'value' => $operator,
                ];
            }
        }

        return $this;
    }

    /**
     * 排序
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string|array $order 排序表达式
     * @return this
    */
    public function order($order) {
        if (is_array($order)) {
            foreach ($order as $v) {
                $this->order[] = $v;
            }
        } else {
            $order = trim(preg_replace("/\s(?=\s)/","\\1", $order));
            $list = explode(',', $order);
            foreach ($list as $v) {
                $arr = explode(' ', $v);
                $this->order[] = [
                    $arr[0],
                    $arr[1] ?? 'desc',
                ];
            }
        }
        return $this;
    }
    
    /**
     * 新增数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @return bool
    */
    public function insert($data) {
        if ($this->saveFieldCheck($data) == false) return false;
        $id = $data[$this->primary];
        // 更新到HASH
        $res = $this->Redis->HMSET($this->hash_key.$id, $data);
        if ($res == false) return $this->error('HASH表插入失败：'.$id);
        // 更新到GEO
        if (!empty($this->geo_rule['longitude']) && !empty($this->geo_rule['latitude'])) {
            $longitude = $this->geo_rule['longitude'];
            $latitude = $this->geo_rule['latitude'];
            if (isset($data[$longitude]) && isset($data[$latitude])) {
                $res = $this->Redis->geoadd($this->geo_key, $data[$longitude], $data[$latitude], $id);
                if ($res == false) return $this->error('HASH表更新完成，GEO表插入失败：'.$id);
            }
        }
        // 更新查询规则
        foreach ($this->query_rule as $field => $rules) {
            if (isset($data[$field]) == false) continue;

            foreach ($rules as $rule) {
                $rule = strtolower($rule);
                switch ($rule) {
                    case 'equal': // 等于
                        $md5 = md5($data[$field]);
                        $res = $this->Redis->hset($this->equal_key.$field.':'.$md5, $id, 1);
                    break;
                    case 'range': // 范围
                        $res = $this->Redis->zadd($this->rand_key.$field, $data[$field], $id);
                    break;
                }
            }
        }
        // 标记回写
        $this->pushTimer($id);
        return true;
    }

    /**
     * 新增数据[批量]
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @param int $oneMax 批量一次最大插入数据量
     * @return false|int 成功返回插入记录数
    */
    public function insertAll($data, $oneMax=1000) {
        $all = [];
        $i = 0;
        $num = 0;
        foreach ($data as $k=>$v) {
            if ($num == $oneMax) {
                $i++;
                $num = 0;
            }
            
            $all[$i][] = $v;
            $num++;
        }
        
        // 批量执行 - 此处可以使用多进程
        $channel = new \Swoole\Coroutine\Channel;
        foreach ($all as $k=>$list) {
            //创建子进程
            go(function () use ($channel, $list){
                $num = 0;
                foreach ($list as $data) {
                    $res = $this->insert($data);
                    if ($res) $num++;
                }
                $channel->push($num);
            });
        }
        // 获取结果
        $total = 0;
        foreach ($all as $k=>$json) {
            $total += $channel->pop();
        }
        // 关闭管道
        $channel->close();
        
        return $total;
    }

    /**
     * 查询【核心函数】
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-11
     * @param int $internal 内部调用时的场景  1.count 2.其他
     * @return false|array
    */
    public function select($internal=false) {
        // 是否直接主键查询
        if ($this->id) {
            $hash_key = $this->hash_key.$this->id;
            if ($internal == false) $this->clean_up();
            if ($this->field) {
                $res = $this->Redis->HMGET($hash_key, $this->field);
            } else {
                $res = $this->Redis->HGETALL($hash_key);
            }
            if (!$res) return false;
            return [$res];
        }
        $geo = [];
        // 先从GEO中拉取
        if (!empty($this->geo)) {
            foreach ($this->geo as $v) {
                $arr = $this->Redis->rawCommand('georadius', $this->geo_key, $v['longitude'], $v['latitude'], $v['range'], 'km', 'WITHCOORD', 'WITHDIST', 'COUNT', 100000, 'ASC');
		        if (!empty($arr)) {
                    foreach ($arr as $val) {
                        // 标记主键为key
                        $geo[$val[0]] = 1;
                    }
                }
            }
        }

        // 再从where中查找
        $list = [];

        // 等于条件先找
        $eq_num = 1;
        foreach ($this->where as $field=>$rules) {
            foreach ($rules as $v) {
                $rule = strtolower($v['rule']);
                switch ($rule) {
                    // 等于查找
                    case 'eq':
                    case '=':
                        $vars = is_array($v['value']) ? $v['value'] : [$v['value']];
                        $query_data = [];
                        foreach ($vars as $data) {
                            $md5 = md5($data);
                            $arr = $this->Redis->hkeys($this->equal_key.$field.':'.$md5);
                            if (!empty($arr)) {
                                foreach ($arr as $id) {
                                    //  AND判断
                                    if (!empty($this->geo)) {
                                        if (isset($geo[$id]) == false) {
                                            continue;
                                        }
                                    }
                                    if ($eq_num != 1) {
                                        if (isset($list[$id]) == false) {
                                            continue;
                                        }
                                    }
                                    // 标记主键为key
                                    $query_data[$id] = 1;
                                }
                            }
                        }

                        $list = $query_data;
                        $eq_num++;
                        unset($this->where[$field]);
                    break;
                }
            }
        }
        // 再从范围中查找
        foreach ($this->where as $field=>$rules) {
            foreach ($rules as $v) {
                $rule = strtolower($v['rule']);
                switch ($rule) {
                    // 范围查找
                    case 'rand':
                    case '><':
                        $query_data = [];
                        $value = $v['value'];
                        $arr = $this->Redis->ZRANGEBYSCORE($this->rand_key.$field, $value[0], $value[1]);
                        if (!empty($arr)) {
                            foreach ($arr as $id) {
                                $where[$field]['rand'] = $id;
                                //  AND判断
                                if (!empty($this->geo)) {
                                    if (isset($list[$id]) == false) {
                                        continue;
                                    }
                                } 
                                if (isset($list[$id]) == false) {
                                    continue;
                                }
                                // 标记主键为key
                                $query_data[$id] = 1;
                            }
                        }
                        $list = $query_data;
                    break;
                }
            }
        }
        
        // 查询详情
        $ret = [];
        foreach ($list as $id=>$lable) {
            if ($this->field) {
                $res = $this->Redis->HMGET($this->hash_key.$id, $this->field);
            } else {
                $res = $this->Redis->HGETALL($this->hash_key.$id);
            }
            if ($res) $ret[] = $res;
        }

        // 模糊匹配
        if (!empty($this->like)) {
            foreach ($ret as $k => $v) {
                foreach ($this->like as $rule) {
                    // 需要查询的字段不存在
                    if (isset($v[$rule['field']]) == false) {
                        unset($ret[$k]);
                        break;
                    }
                    // 存在则开始匹配
                    if ($rule['operator'] == 's%') {
                        $lt = stripos($v[$rule['field']], $rule['value']);
                        $rt = strlen(substr($v[$rule['field']], 0, strripos($v[$rule['field']], $rule['value'])));
                        
                        if ($lt != $rt) {
                            unset($ret[$k]);
                            break;
                        } else if ($lt == 0 && $rt == 0 && $v[$rule['field']] != $rule['value']) {
                            unset($ret[$k]);
                            break;
                        }
                    } else if ($rule['operator'] == '%s%') {
                        if (stripos($v[$rule['field']], $rule['value']) === false) {
                            unset($ret[$k]);
                            break;
                        }
                    } else  if ($rule['operator'] == '%s') {
                        if (stripos($v[$rule['field']], $rule['value']) !== 0) {
                            unset($ret[$k]);
                            break;
                        }
                    }
                }
            }
        }
        
        // 获取数量
        if ($internal == 1) {
            return count($ret);
        }

        // 排序
        if (!empty($this->order)) {
            $vars = [];
            $vars[] = $ret;
            foreach ($this->order as $v) {
                $vars[] = $v[0];
                if (strtolower($v[1]) == 'asc') {
                    $vars[] = SORT_ASC;
                } else {
                    $vars[] = SORT_DESC;
                }
            }
            $ret = $this->sortArrByManyField($vars);
        }

        // 分页限制条目
        if (!empty($this->page) || !empty($this->limit)) {
            if (!empty($this->page)) {
                if ($this->page['left'] <= 1) {
                    $left = 0;
                } else {
                    $left = ($this->page['left']-1) * $this->page['right'];
                }
                $right = $left + $this->page['right'];
            } else if (!empty($this->limit)) {
                $left = $this->limit['left'];
                $right = $this->limit['right'];
            }
            $list = [];
            foreach ($ret as $k => $v) {
                if ($k >= $left && $k < $right) {
                    $list[] = $v;
                }
            }
            $ret = $list;
        }

        if ($internal == false) {
            $this->clean_up();
        }
        // 条件查询
        return $ret;
    }

    /**
     * 获取第一条数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @return false|array
    */
    public function find() {
        $res = $this->select();
        return current($res);
    }

    /**
     * 获取第一条数据的某个值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 获取的字段 
     * @return false|mixed
    */
    public function value($field) {
        $res = $this->find();
        if (!$res) return false;
        if (!isset($res[$field])) return false;
        return $res[$field];
    }

    /**
     * 获取记录数
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @return int
    */
    public function count() {
        $res = $this->select(1);
        $this->clean_up();
        return $res;
    }

    /**
     * 获取最大值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 获取的字段 
     * @return false|mixed
    */
    public function max($field) {
        $list = $this->select();
        if (empty($list)) return false;
        $ret = [$list, $field, SORT_DESC];
        $list = $this->sortArrByManyField($ret);
        if (empty($list)) return false;
        $info = current($list);
        return $info[$field] ?? false;
    }

    /**
     * 获取最小值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 获取的字段 
     * @return false|mixed
    */
    public function min($field) {
        $list = $this->select();
        if (empty($list)) return false;
        $ret = [$list, $field, SORT_ASC];
        $list = $this->sortArrByManyField($ret);
        if (empty($list)) return false;
        $info = current($list);
        return $info[$field] ?? false;
    }

    /**
     * 平均值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 获取的字段 
     * @return false|int|float
    */
    public function avg($field) {
        $list = $this->select();
        if (empty($list)) return false;
        $total = 0;
        $num = 0;
        foreach ($list as $k => $v) {
            $num++;
            if (isset($v[$field]) && is_numeric($v[$field])) {
                $total += $v[$field];
            }
        }
        if ($total == 0) return $total;
        return \x\common\Money::round($total/$num, 2);
    }

    /**
     * 总值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 获取的字段 
     * @return false|int|float
    */
    public function sum($field) {
        $list = $this->select();
        if (empty($list)) return false;
        $total = 0;
        foreach ($list as $k => $v) {
            if (isset($v[$field]) && is_numeric($v[$field])) {
                $total += $v[$field];
            }
        }
        return $total;
    }

    /**
     * 删除
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @return bool
    */
    public function delete() {
        $this->field = [];
        $list = $this->select();
        if (empty($list)) return true;
        foreach ($list as $v) {
            $id = $v[$this->primary];
            // 删除主体
            $this->Redis->del($this->hash_key.$id);
            // 删除GEO
            if (!empty($this->geo_rule['longitude']) && !empty($this->geo_rule['latitude'])) {
                $longitude = $this->geo_rule['longitude'];
                $latitude = $this->geo_rule['latitude'];
                if (isset($v[$longitude]) && isset($v[$latitude])) {
                    $this->Redis->zrem($this->geo_key, $id);
                }
            }
            // 删除查询规则
            foreach ($v as $field => $value) {
                $md5 = md5($value);
                // 等于条件
                $this->Redis->hdel($this->equal_key.$field.':'.$md5, $id);
                // 范围条件
                $this->Redis->zrem($this->rand_key.$field, $id);
            }
        }
        return true;
    }

    /**
     * 自增
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 操作字段 
     * @param int|float $num 数字 
     * @return false|int 成功返回数量
    */
    public function setInc($field, $num=1) {
        $this->field = [];
        $list = $this->select();
        $this->clean_up();
        if (empty($list)) return false;

        $i = 0;
        foreach ($list as $info) {
            $id = $info[$this->primary];
            // 先自增
            $res = $this->Redis->HINCRBYFLOAT($this->hash_key.$id, $field, $num);
            if (!$res) {
                continue;
            }
            // 后更改查询条件
            // 等于
            $md5 = md5($info[$field]);
            $res = $this->Redis->hdel($this->equal_key.$field.':'.$md5, $id);
            if ($res) {
                $md5 = md5($info[$field]+$num);
                $this->Redis->hset($this->equal_key.$field.':'.$md5, $id, 1);
            }
            // 范围
            $res = $this->Redis->zrem($this->rand_key.$field, $id);
            if ($res) {
                $this->Redis->zadd($this->rand_key.$field, $info[$field]+$num, $id);
            }
            
            // 标记回写
            $this->pushTimer($id);
            $i++;
        }
        return $i;
    }

    /**
     * 自减
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 操作字段 
     * @param int|float $num 数字 
     * @return false|int 成功返回数量
    */
    public function setDec($field, $num=1) {
        $num = 0-$num;
        return $this->setInc($field, $num);
    }

    /**
     * 更新
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param array $data 更新内容 
     * @return false|int 成功返回数量
    */
    public function update($data) {
        $this->field = [];
        $list = $this->select();
        $this->clean_up();
        if (empty($list)) return false;

        $i = 0;
        foreach ($list as $info) {
            $id = $info[$this->primary];
            unset($info[$this->primary]);

            if (empty($info)) continue;
            
            // 字段更新
            $res = $this->Redis->hmset($this->hash_key.$id, $data);
            if (!$res) continue;  
            // 删除GEO
            if (!empty($this->geo_rule['longitude']) && !empty($this->geo_rule['latitude'])) {
                $longitude = $this->geo_rule['longitude'];
                $latitude = $this->geo_rule['latitude'];
                if (isset($data[$longitude]) && isset($data[$latitude])) {
                    $this->Redis->zrem($this->geo_key, $id);
                    $this->Redis->geoadd($this->geo_key, $data[$longitude], $data[$latitude], $id);
                }
            }
            // 删除查询规则
            foreach ($data as $field => $value) {
                $md5 = md5($info[$field]);
                // 等于条件
                $this->Redis->hdel($this->equal_key.$field.':'.$md5, $id);
                // 范围条件
                $this->Redis->zrem($this->rand_key.$field, $id);
            }
            // 更新查询规则
            foreach ($this->query_rule as $field => $rules) {
                if (isset($data[$field]) == false) continue;

                foreach ($rules as $rule) {
                    $rule = strtolower($rule);
                    switch ($rule) {
                        case 'equal': // 等于
                            $md5 = md5($data[$field]);
                            $this->Redis->hset($this->equal_key.$field.':'.$md5, $id, 1);
                        break;
                        case 'range': // 范围
                            $this->Redis->zadd($this->rand_key.$field, $data[$field], $id);
                        break;
                    }
                }
            }
            // 标记回写
            $this->pushTimer($id);
            $i++;
        }

        return $i;
    }

    /**
     * 获取错误信息
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @return string|null
    */
    public function getError() {
        return $this->error_msg;
    }

    /**
     * 获取所有需要回写的记录
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-12
     * @return array
    */
    public function getWriteBack() {
        $arr = $this->Redis->hkeys($this->timer_key);
        $list = [];
        if (!empty($arr)) {
            foreach ($arr as $id) {
                $info = $this->Redis->HGETALL($this->hash_key.$id);
                if ($info) $list[] = $info;
            }
        }
        return $list;
    }

    /**
     * 删除一条回写记录
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-12
     * @param string|ing $id 主键值
     * @return bool
    */
    public function deleteWriteBack($id) {
        return $this->Redis->hdel($this->timer_key, $id);
    }

    /**
     * 回写记录
     * @author 小黄牛
	 * @version v2.5.26 + 2022-05-11
     * @param string|ing $id 主键值
     * @return bool
    */
    private function pushTimer($id) {
        if ($this->timer == false) return true;
        return $this->Redis->hset($this->timer_key, $id, 1);
    }

    /**
     * 终结函数用完就得初始化对象，防止污染下一次操作
     * @author 小黄牛
	 * @version v2.5.26 + 2022-05-11
    */
    private function clean_up() {
        $this->id = null;
        $this->geo = [];
        $this->like = [];
        $this->where = [];
        $this->field = [];
        $this->limit = [];
        $this->page = [];
        $this->order = [];
        $this->error_msg = null;
    }

    /**
     * 更新数据格式检查
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @param string $type 检查类型 insert|update
     * @return bool
    */
    private function saveFieldCheck($data, $type="insert") {
        // 主键字段不能为空
        if (empty($this->primary)) return $this->error('模型对象中，$primary主键字段属性未设置');
        // 构建字段不能为空
        if (empty($this->field_rule)) return $this->error('模型对象中，$field_rule构建模型属性未设置');
        // 不存在主键
        if ($type == "insert" && isset($data[$this->primary]) == false) return $this->error('insert时，不能缺少主键字段');
        
        foreach ($this->field_rule as $field) {
            if (isset($data[$field]) == false) return $this->error('insert时，缺少必传字段：'.$field);
        }
        return true;
    }
    
    /**
     * 写入错误原因
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $msg 原因
     * @return false
    */
    private function error($msg) {
        $this->error_msg = $msg;
        return false;
    }

    /**
     * 多字段同时排序
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $args array_multisort所需参数
     * @return null|array
    */
    private function sortArrByManyField($args){
        if(empty($args)){
            return null;
        }
        $arr = array_shift($args);
        if(!is_array($arr)){
            throw new \Exception("排序失败，需要排序的数组集并不是一个正确的数组");
        }
        foreach($args as $key => $field){
            if(is_string($field)){
                $temp = [];
                foreach($arr as $index=> $val){
                    $temp[$index] = $val[$field];
                }
                $args[$key] = $temp;
            }
        }
        $args[] = &$arr;// 引用值
        call_user_func_array('array_multisort',$args);
        $ret = array_pop($args);
        unset($args);
        return $ret;
    }
}