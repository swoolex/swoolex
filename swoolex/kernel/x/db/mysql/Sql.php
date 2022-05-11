<?php
/**
 * +----------------------------------------------------------------------
 * Mysql-SQL-ORM-构造器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\db\mysql;
use design\AbstractMysqlSql;

class Sql extends AbstractMysqlSql {

    /**
     * 表名
    */
    private $table;
    /**
     * 别名
    */
    private $alias;
    /**
     * 条件
    */
    private $where;
    /**
     * 字段
    */
    private $field = '*';
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
     * 筛选
    */
    private $having;
    /**
     * 分组
    */
    private $group;
    /**
     * 链表
    */
    private $join;
    /**
     * 表前缀
    */
    private $prefix;
    /**
     * TestCase唯一别名
    */
    public $test_case;
    /**
     * 不执行sql
    */
    private $debug = false;

    /**
     * 注入Db
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @param Db $Db
    */
    public function __construct($Db) {
        $this->Db = $Db;
    }

    /**
     * 销毁Db
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
    */
    public function __destruct() {
        $this->Db = null;
    }

    /**
     * 调试SQL语句
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @return this
    */
    public function debug() {
        $this->debug = true;
        return $this;
    }

    /**
     * 选择表
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $table 表名
     * @return this
    */
    public function name($table) {
        $this->clean_up();
        # 获取数据表前缀
        $this->prefix = $this->Db->prefix;
        $this->table = '`'.$this->prefix.$table.'`';
        return $this;
    }
    
    /**
     * 选择表（不带前缀）
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $table 表名 OR 子查询语句
     * @return this
    */
    public function table($table) {
        $this->clean_up();
        # 获取数据表前缀
        $this->prefix = $this->Db->prefix;
        $this->table = '`'.$table.'`';
        return $this;
    }

    /**
     * 别名
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $as 别名
     * @return this
    */
    public function alias($as) {
        $this->alias = $as;
        return $this;
    }
    /**
     * 条件
     * @author 小黄牛
     * @version v1.2.8 + 2020.07.28
     * @param string $field 可以为批量表达式，也可以是字段
     * @param string $operator 表达式
     * @param string $value 条件
     * @return this
    */
    public function where($field, $operator=null, $value=false) {
        if (!$field) return $this;

        if (is_array($field) == false && ($operator === null || $operator === '')) {
            $is_list = [
                ' ',
                '=',
                '>',
                '<',
                '!',
            ];
            $status = false;
            foreach ($is_list as $v) {
                if (strpos($field, $v) !== false) {
                    $status = true;
                    break;
                }
            }
            if ($status == false) {
                return $this;
            }
        }

        if (is_array($field)) {
            foreach ($field as $v) {
                if (is_string($v)) {
                    $this->where[] = [$v, 1];
                } else {
                    if (stripos($v[0], '|') !== false) {
                        $where = '(';
                        $array = explode('|', $v[0]);
                        foreach ($array as $val) {
                            $where .= '('.$val.' '.$v[1].' '.$this->int_string($v[2]).') OR ';
                        }
                        $where = rtrim($where, 'OR ').')';
                        $this->where[] = [$where, 1];
                    } else {
                        if ($v[2] === null) {
                            $this->where[] = [($v[0].' '.$v[1]), 1];
                        } else {
                            $this->where[] = [($v[0].' '.$v[1].' '.$this->int_string($v[2])), 1];
                        }
                    }
                }
            }
        } else {
            if ($value !== false) {
                $this->where[] = [($field.' '.$operator.' '.$this->int_string($value)), 1];
            } else if ($operator !== null) {
                $this->where[] = [($field.'='.$this->int_string($operator)), 1];
            } else {
                $this->where[] = [$field, 1];
            }
        }
        return $this;
    }
    /**
     * 条件IN
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @param string $field 只可以是字段
     * @param string $in 条件
     * @return this
    */
    public function whereIn($field, $in) {
        if (stripos($in, '(') === false) {
            $in = '('.$in.')';
        }
        $this->where[] = [$field.' IN '.$in, 1];
        return $this;
    }
    /**
     * 条件NotIn
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @param string $field 只可以是字段
     * @param string $in 条件
     * @return this
    */
    public function whereNotIn($field, $in) {
        if (stripos($in, '(') === false) {
            $in = '('.$in.')';
        }
        $this->where[] = [$field.' NOT IN '.$in, 1];
        return $this;
    }
    /**
     * 条件OR
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @param string $field 只可以是字段
     * @param string $operator 表达式
     * @param string $value 条件
     * @return this
    */
    public function whereOr($field, $operator=null, $value=false) {
        if (!$field) return $this;
        
        if ($value !== false) {
            $this->where[] = [($field.' '.$operator.' '.$this->int_string($value)), 2];
        } else if ($operator !== null) {
            $this->where[] = [($field.'='.$this->int_string($operator)), 2];
        } else {
            $this->where[] = [$field, 2];
        }

        return $this;
    }
    /**
     * 时间条件
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 时间字段，必须为int类型
     * @param string $where 表达式
     * @param string $data 内容
     * @return this
    */
    public function whereTime($field, $where, $data=null) {
        $where = str_replace(' ', '', strtolower($where));
        $namespace = '\\x\db\\mysql\query\\'.$where.'::run';
        $ret = '';

        switch ($where) {
            case 'today': $ret = $namespace($field, $where, $data); break;
            case 'yesterday': $ret = $namespace($field, $where, $data); break;
            case 'week': $ret = $namespace($field, $where, $data); break;
            case 'lastweek': $ret = $namespace($field, $where, $data); break;
            case 'month': $ret = $namespace($field, $where, $data); break;
            case 'lastmonth': $ret = $namespace($field, $where, $data); break;
            case 'year': $ret = $namespace($field, $where, $data); break;
            case 'lastyear': $ret = $namespace($field, $where, $data); break;
            case 'between': $ret = $namespace($field, $where, $data); break;
            case 'notbetween': $ret = $namespace($field, $where, $data); break;
            default:
                if (!is_numeric($data)) $data = strtotime($data);
                $ret = $field.$where.$data;
            break;
        }
        
        if ($ret) {
            $this->where[] = [$ret, 1];
        }

        return $this;
    } 
    /**
     * 打印字段
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 显示字段
     * @return this
    */
    public function field($field) {
        $this->field = $field;
        return $this;
    }
    /**
     * 指定条数
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param int $left 左
     * @param int $right 右
     * @return this
    */
    public function limit($left, $right=null) {
        $this->limit = [
            'left' => $left, 
            'right' => $right
        ];
        return $this;
    }
    /**
     * 分页条数
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
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
     * 排序
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $order 排序表达式
     * @return this
    */
    public function order($order) {
        $this->order = $order;
        return $this;
    }
    /**
     * 筛选
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 筛选条件
     * @return this
    */
    public function having($field) {
        $this->having = $field;
        return $this;
    }
    /**
     * 分组
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 分组字段
     * @return this
    */
    public function group($field) {
        $this->group = $field;
        return $this;
    }
    /**
     * 测试用例别名设置
     * @author 小黄牛
     * @version v1.2.17 + 2020.10.29
     * @param string $name 别名
     * @return this
    */
    public function test($name) {
        $this->test_case = $name;
        return $this;
    }
    /**
     * 链表
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.15
     * @param string $table 链表表达式
     * @param string $on  链表条件
     * @param string $join 链表方式
     * @param bool $status 是否自动使用表前缀
     * @return this
    */
    public function join($table, $on, $join='LEFT', $status=true) {
        if ($status) $table = $this->prefix.$table;
        $this->join[] = [
            'table' => $table, 
            'on' => $on,
            'join' => strtoupper($join),
        ];
        return $this;
    }
    /**
     * 缓存组件
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @param string $key 缓存标识
     * @return this
    */
    public function cache($key=null) {
        $this->cache_status = true;
        $this->cache_key = $key;
        return $this;
    }

    /**
     * 单独设置缓存有效期
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @param mixed $expire_time 过期时间，0为永久
     * @return this
    */
    public function expire($expire_time) {
        $this->expire_time = $expire_time;
        return $this;
    }

    /**
     * 终结方法-分页查询
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @param int $size 每页数
     * @param array $query 分页配置参数
     * @return bool|object
    */
    public function paginate($size, $query=null) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        // 分页配置
        if (empty($query)) {
            $options = \x\Config::get('view.paginate');
        } else {
            $options = array_merge(\x\Config::get('view.paginate'), $query);
        }

        $field = $options['var_page'];
        $param = \x\Request::get();
        $page = 1;
        if (!empty($param[$field])) {
            $page = $param[$field];
        }

        // 分页查询的SQL
        $this->page($page, $size);
        $select_sql = $this->select_sql(false);
        // 总数查询的SQL
        $this->page = null;
        $this->field = 'COUNT(*) AS '.$this->ploy_alias;
        $total_sql = $this->select_sql(false);

        if ($this->debug==false) {
            $start_time = microtime(true);

            // 查出总记录数
            $res = $this->Db->query($total_sql, false);
            if ($res === false) return false;
            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;
            $total = $info[$this->ploy_alias];

            // 查询缓存
            $cache = $this->select_cache($select_sql);
            if ($cache['status'] == true) {
                $list = json_decode($cache['data'], true);
            } else {
                $res = $this->Db->query($select_sql, false);
                $this->clean_up();
                $end_time = microtime(true);
                $this->record($select_sql, $start_time, $end_time);
                if ($res === false) return false;

                $list = $res->fetchAll(\PDO::FETCH_NAMED);
                if (empty($list)) $list = [];
                
                // 写入缓存
                $this->create_cache($select_sql, $list);
            }
            
            $class = $options['type'];
            return new $class($list, $size, $page, $total, $options);
        }

        return $select_sql;
    }
    /**
     * 终结方法-查询
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param bool $status 是否不执行
     * @return bool|array
    */
    public function select($status=true) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $sql = $this->select_sql(false);

        if ($status && $this->debug==false) {
            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return json_decode($cache['data'], true);
            }
            
            $start_time = microtime(true);
            $this->clean_up();
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;
            $list = $res->fetchAll(\PDO::FETCH_NAMED);
            if (empty($list)) $list = [];
            
            // 写入缓存
            $this->create_cache($sql, $list);

            return $list;
        }

        return $sql;
    }
    /**
     * 终结方法-查询-固定一条
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param bool $status 是否不执行
     * @return bool|array
    */
    public function find($status=true) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $sql = $this->select_sql(true);

        if ($status && $this->debug==false) {
            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return json_decode($cache['data'], true);
            }

            $start_time = microtime(true);
            $this->clean_up();
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;
            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;
            
            // 写入缓存
            $this->create_cache($sql, $info);
            
            return $info;
        }

        return $sql;
    }
    /**
     * 终结方法-子查询构造器
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @return string
    */
    public function buildSql() {
        $sql = $this->select_sql(false);
        $this->clean_up();

        return ' ( '.rtrim($sql, ';').' ) ';
    }
    /**
     * 终结方法-删除
     * @author 小黄牛
     * @version v1.2.2 + 2020.07.20
     * @param bool $status 是否不执行
     * @return bool
    */
    public function delete($status=true) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        // 无where条件不允许执行
        if (empty($this->where)) return false;

        $sql = 'DELETE';
        $sql .= ' FROM';
        $sql .= ' '.$this->table;
        $sql = $this->where_sql($sql);
        if ($this->order) {
            $sql .= ' ORDER BY '.$this->order;
        }
        if ($this->limit) {
            $sql .= ' LIMIT '.$this->limit['left'];
        }
        $sql .= ';';

        if ($status && $this->debug==false) {
            $this->clean_up();
            $start_time = microtime(true);
            $res = $this->Db->exec($sql);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            return $res;
        }
        return $sql;
    }
    /**
     * 终结方法-修改
     * @author 小黄牛
     * @version v1.2.2 + 2020.07.20
     * @return bool
    */
    public function update($data) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        // 无where条件不允许执行
        if (empty($this->where)) return false;

        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET ';

        foreach ($data as $key=>$val) {
            $sql .= '`'.$key.'`='.$this->int_string($val).',';
        }
        $sql = rtrim($sql, ',');
        $sql = $this->where_sql($sql).';';

        if ($this->debug==false) {
            $this->clean_up();
            $start_time = microtime(true);
            $res = $this->Db->exec($sql);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            return $res;
        }
        return $sql;
    }
    /**
     * 终结方法-新增
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param int $oneMax 批量一次最大插入数据量
     * @return bool
    */
    public function insert($data, $oneMax=1000) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $list = [];
        # 检测是否单个新增
        if (!isset($data[0])) {
            $list[] = $data;
        } else {
            $list = $data;
        }

        $field_list = reset($list);
        $all = [];
        $i = 0;
        $num = 0;
        $sql = 'INSERT INTO';
        $sql .= ' '.$this->table;
        $field = ' (';
        foreach ($field_list as $key=>$val) {
            $field .= '`'.$key.'`,';
        }
        $sql .= rtrim($field, ',').')';
        $sql .= ' VALUES ';
        $all[$i] = $sql;
        
        foreach ($list as $k=>$v) {
            if ($num == $oneMax) {
                $i++;
                $num = 0;
                
                $sql = 'INSERT INTO';
                $sql .= ' '.$this->table;
                $field = ' (';
                foreach ($field_list as $key=>$val) {
                    $field .= '`'.$key.'`,';
                }
                $sql .= rtrim($field, ',').')';
                $sql .= ' VALUES ';
                
                $all[$i] = $sql;
            }
    
            $field = '(';
            foreach ($v as $val) {
                $field .= $this->int_string($val).',';
            }
            $sql = rtrim($field, ',').'),';
            $all[$i] .= $sql;
            $num++;
        }
        
        
        if ($this->debug==false) {
            $this->clean_up();
            
            $this->Db->begin(true);
            foreach ($all as $sql) {
                $sql = rtrim($sql, ',').';';
                try {
                    $start_time = microtime(true);
                    $res = $this->Db->exec($sql);
                    $end_time = microtime(true);
                    $this->record($sql, $start_time, $end_time);
                } catch (\Exception $e) {
                    throw new \Exception($e->getMessage());
                    $this->Db->rollback();
                    return false;
                }
                
                if (!$res) {
                    $this->Db->rollback();
                    return false;
                }
            }
            $this->Db->commit();
            return true;
        }
        return $all;
    }
    /**
     * 终结方法-新增
     * @author 小黄牛
     * @version v1.1.10 + 2020.07.17
     * @return bool|int
    */
    public function insertGetId($data) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $sql = 'INSERT INTO';
        $sql .= ' '.$this->table;

        $field = ' (';
        foreach ($data as $key=>$val) {
            $field .= '`'.$key.'`,';
        }
        $sql .= rtrim($field, ',').')';

        $sql .= ' VALUES ';
        $field = '(';
        foreach ($data as $val) {
            $field .= $this->int_string($val).',';
        }
        $sql .= rtrim($field, ',').');';
        
        if ($this->debug == false) {
            $this->clean_up();
            
            $start_time = microtime(true);
            $res = $this->Db->exec($sql);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if (!$res) return false;

            $sql = 'SELECT LAST_INSERT_ID() as num;';
            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $list = $res->fetchAll(\PDO::FETCH_NAMED);
            if (empty($list)) return false;
            return $list[0]['num'];
        }
        return $sql;
    }

    /**
     * 自增
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 字段名
     * @param int $num 值
     * @return bool
    */
    public function setInc($field, $num=1) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;
        
        // 无where条件不允许执行
        if (empty($this->where)) return false;

        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET';
        $sql .= ' `'.$field.'`='.$field.'+'.$num;
        $sql = $this->where_sql($sql).';';

        if ($this->debug==false) {
            $this->clean_up();
            $start_time = microtime(true);
            $res = $this->Db->exec($sql);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            return $res;
        }
        return $sql;
    }
    /**
     * 自减
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $field 字段名
     * @param int $num 值
     * @return bool
    */
    public function setDec($field, $num=1) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        // 无where条件不允许执行
        if (empty($this->where)) return false;
        
        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET';
        $sql .= ' `'.$field.'`='.$field.'-'.$num;
        $sql = $this->where_sql($sql).';';
        
        if ($this->debug==false) {
            $this->clean_up();
            $start_time = microtime(true);
            $res = $this->Db->exec($sql);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            return $res;
        }
        return $sql;
    }
    /**
     * 聚合操作(统计数量)
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return mixed
    */
    public function count($field=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $field = $field ?: '*';
        $this->field = 'COUNT('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);

        if ($this->debug==false) {
            $this->clean_up();

            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;
            
            // 写入缓存
            $this->create_cache($sql, $info[$this->ploy_alias]);
            
            return $info[$this->ploy_alias];
        }

        return $sql;
    }
    /**
     * 聚合操作(获取最大值)
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return mixed
    */
    public function max($field=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        if ($field == false) return false;
        $this->field = 'MAX('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        
        if ($this->debug==false) {
            $this->clean_up();
            
            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;

            // 写入缓存
            $this->create_cache($sql, $info[$this->ploy_alias]);

            return $info[$this->ploy_alias];
        }

        return $sql;
    }
    /**
     * 聚合操作(获取最小值)
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return mixed
    */
    public function min($field=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        if ($field == false) return false;
        $this->field = 'MIN('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        
        if ($this->debug==false) {
            $this->clean_up();
            
            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;

            // 写入缓存
            $this->create_cache($sql, $info[$this->ploy_alias]);

            return $info[$this->ploy_alias];
        }

        return $sql;
    }
    /**
     * 聚合操作(获取平均值)
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return mixed
    */
    public function avg($field=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        if ($field == false) return false;
        $this->field = 'AVG('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        
        if ($this->debug==false) {
            $this->clean_up();

            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;

            // 写入缓存
            $this->create_cache($sql, $info[$this->ploy_alias]);

            return $info[$this->ploy_alias];
        }

        return $sql;
    }
    /**
     * 聚合操作(获取总分)
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return mixed
    */
    public function sum($field=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        if ($field == false) return false;
        $this->field = 'SUM('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);

        if ($this->debug==false) {
            $this->clean_up();

            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;

            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;

            // 写入缓存
            $this->create_cache($sql, $info[$this->ploy_alias]);

            return $info[$this->ploy_alias];
        }

        return $sql;
    }
    /**
     * 获取某个字段的值
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @param string $field 字段名
     * @return false|mixed
    */
    public function value($field) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        if ($field == false) return false;
        $this->field = $field;
        $sql = $this->select_sql(true);

        if ($this->debug==false) {
            $this->clean_up();

            // 查询缓存
            $cache = $this->select_cache($sql);
            if ($cache['status'] == true) {
                return $cache['data'];
            }

            $start_time = microtime(true);
            $res = $this->Db->query($sql, false);
            $end_time = microtime(true);
            $this->record($sql, $start_time, $end_time);
            if ($res === false) return false;
            
            $info = $res->fetch(\PDO::FETCH_NAMED);
            if (empty($info)) return false;

            // 写入缓存
            $this->create_cache($sql, $info[$field]);

            return $info[$field];
        }

        return $sql;
    }
    /**
     * 执行原生SQL
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $sql 原生SQL
     * @return mixed
    */
    public function query($sql, $status=false) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $start_time = microtime(true);
        $res = $this->Db->query($sql, $status);
        $end_time = microtime(true);
        $this->record($sql, $start_time, $end_time);
        $info = $res->fetchAll(\PDO::FETCH_NAMED);
        if (empty($info)) return false;

        return $info;
    }
    /**
     * 执行原生SQL
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $sql 原生SQL
     * @return mixed
    */
    public function exec($sql) {
        $test = $this->testcase();
        if ($test != 'SwooleXTestCase') return $test;

        $start_time = microtime(true);
        $res = $this->Db->exec($sql);
        $end_time = microtime(true);
        $this->record($sql, $start_time, $end_time);
        return $res;
    }
    /**
     * 查询相关通用语句组装
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $SQL sql语句
     * @return string
    */
    private function where_sql($sql) {
        if ($this->where) {
            $list_sql = [];
            $status = false;
            $top_type = false;
            $num = count($this->where);

            foreach ($this->where as $k=>$v) {
                if ($k == 0) {
                    $list = [];
                    $list[] = $v;
                } else {
                    $type = $this->where[($k-1)][1];
                    if ($v[1] != $this->where[($k-1)][1]) {
                        $list_sql[] = [$list, $type];
                        $list = [];
                    }
                    $list[] = $v;
                }
            }
            if (isset($list)) {
                $list_sql[] = [$list, $v[1]];
            }
            $where = '';
            $num = count($list_sql);
            foreach ($list_sql as $k=>$v) {
                if ($v[1] == 1) {
                    $sql_str = '(';
                    foreach ($v[0] as $val) {
                        $sql_str .= $val[0].' AND ';
                    }
                    $sql_str = rtrim($sql_str, 'AND ').')';
                } else {
                    $sql_str = '';
                    foreach ($v[0] as $val) {
                        $sql_str .= $val[0].' OR ';
                    }
                    $sql_str = rtrim($sql_str, 'OR ');
                }

                if ($k < $num) {
                    if (!empty($top_sql)) {
                        $where .= '('.$top_sql.' OR '.$sql_str.')';
                    } else {
                        $where .= ' AND ';
                    }
                    $top_type = $list_sql[($k+1)][1] ?? 0;
                    if ($v[1] == 1 && $top_type == 2) {
                        $top_sql = $sql_str;
                    } else {
                        if (empty($top_sql)) $where .= $sql_str;
                        $top_sql = '';
                    }
                }
            }
            $where = ltrim($where, ' AND ');
            $sql .= ' where '.$where;
        }
        return $sql;
    }

    /**
     * 组装查询语句
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param bool $status 是否为单条记录
     * @return string
    */
    private function select_sql($status=false) {
        $sql = 'SELECT';
        $sql .= ' '.$this->field;
        $sql .= ' FROM';
        $sql .= ' '.$this->table;
        if ($this->alias) {
            $sql .= ' AS '.$this->alias;
        }
        if ($this->join) {
            foreach ($this->join as $v) {
                if (substr($v['table'] , 0 , 1) == '(') {
                    $sql .= ' '.$v['join'].' JOIN '.$v['table'].' ON '.$v['on'];
                } else {
                    $array = explode(' ', $v['table']);
                    $table = '';
                    foreach ($array as $key=>$val) {
                        if ($key == 0) {
                            $table .= $val.' AS ';
                        } else {
                            $table .= $val.' ';
                        }
                    }
                    $sql .= ' '.$v['join'].' JOIN '.$table.'ON '.$v['on'];
                }
            }
        }
        $sql = $this->where_sql($sql);
        if ($this->group) {
            $sql .= ' GROUP BY '.$this->group;
        }
        if ($this->having) {
            $sql .= ' HAVING '.$this->having;
        }
        if ($this->order) {
            $sql .= ' ORDER BY '.$this->order;
        }
        if ($status == false) {
            if ($this->page) {
                if ($this->page['left'] <= 1) {
                    $left = 0;
                } else {
                    $left = ($this->page['left']-1) * $this->page['right'];
                }
    
                $sql .= ' LIMIT '.$left;
                $sql .= ','.$this->page['right'];
            } else {
                if ($this->limit) {
                    $sql .= ' LIMIT '.$this->limit['left'];
                    if ($this->limit['right']) {
                        $sql .= ','.$this->limit['right'];
                    }
                }
            }
        } else {
            if ($this->limit) {
                $sql .= ' LIMIT '.$this->limit['left'];
                if ($this->limit['right']) {
                    $sql .= ','.$this->limit['right'];
                }
            } else {
                $sql .= ' LIMIT 1';
            }
        }
        $sql .= ';';

        return $sql;
    }

    /**
     * 获取表名是否已填充
     * @author 小黄牛
     * @version v2.5.20 + 2022-01-06
     * @return null|string
    */
    public function getTable() {
        return $this->table;
    }

    /**
     * 由于是单例，用完就得清除某些共用成员
     * @author 小黄牛
     * @version v1.2.17 + 2020.10.29
    */
    private function clean_up() {
        $this->table = null;
        $this->alias = null;
        $this->where = null;
        $this->field = '*';
        $this->limit = null;
        $this->page = null;
        $this->order = null;
        $this->having = null;
        $this->group = null;
        $this->join = null;
        $this->debug = false;
        $this->test_case = null;
    }

    /**
     * Model类反转调用
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @return object
    */
    public function __call($name, $arguments=[]) {
        return $this->Db->$name(...$arguments);
    }
}