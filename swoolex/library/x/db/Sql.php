<?php
// +----------------------------------------------------------------------
// 数据库构造器
// +----------------------------------------------------------------------
// Copyright (c) 2020 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------
namespace x\db;
use x\db\AbstractSql;

class Sql extends AbstractSql {

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
     * 不执行sql
    */
    private $debug = false;
    /**
     * Db实例
    */
    public $Db;
    /**
     * 统计的别名
    */
    public $ploy_alias = 'swoolex';

    /**
     * 调试SQL语句
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function debug() {
        $this->debug = true;
        return $this;
    }

    /**
     * 选择表
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $table 表名
     * @return void
    */
    public function name($table) {
        # 获取数据表前缀
        $this->prefix = \x\Config::run()->get('mysql.prefix');

        $this->table = $this->prefix.$table;

        return $this;
    }
    
    /**
     * 选择表（不带前缀）
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $table 表名 OR 子查询语句
     * @return void
    */
    public function table($table) {
        $this->table = $table;
        return $this;
    }

    /**
     * 别名
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $as 别名
     * @return void
    */
    public function alias($as) {
        $this->alias = $as;
        return $this;
    }
    /**
     * 条件
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 可以为批量表达式，也可以是字段
     * @param string $operator 表达式
     * @param string $value 条件
     * @return void
    */
    public function where($field, $operator=null, $value=false) {
        if (is_array($field)) {
            foreach ($field as $v) {
                $this->where[] = $v[0].' '.$v[1].' '.$this->int_string($v[2]);
            }
        } else {
            if ($value !== false) {
                $this->where[] = $field.' '.$operator.' '.$this->int_string($value);
            } else if ($operator) {
                $this->where[] = $field.'='.$this->int_string($operator);
            } else {
                $this->where[] = $field;
            }
        }
        return $this;
    } 
    /**
     * 时间条件
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 时间字段，必须为int类型
     * @param string $where 表达式
     * @param string $data 内容
     * @return void
    */
    public function whereTime($field, $where, $data=null) {
        $where = str_replace(' ', '', strtolower($where));
        $namespace = '\\x\db\\query\\'.$where.'::run';
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
            $this->where[] = $ret;
        }

        return $this;
    } 
    /**
     * 打印字段
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 显示字段
     * @return void
    */
    public function field($field) {
        $this->field = $field;
        return $this;
    }
    /**
     * 指定条数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param int $left 左
     * @param int $right 右
     * @return void
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param int $left 左
     * @param int $right 右
     * @return void
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
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $order 排序表达式
     * @return void
    */
    public function order($order) {
        $this->order = $order;
        return $this;
    }
    /**
     * 筛选
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 筛选条件
     * @return void
    */
    public function having($field) {
        $this->having = $field;
        return $this;
    }
    /**
     * 分组
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 分组字段
     * @return void
    */
    public function group($field) {
        $this->group = $field;
        return $this;
    }
    /**
     * 链表
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.15
     * @deprecated 暂不启用
     * @global 无
     * @param string $table 链表表达式
     * @param string $on  链表条件
     * @param string $join 链表方式
     * @param bool $status 是否自动使用表前缀
     * @return void
    */
    public function join($table, $on, $join='LEFT', $status=true) {
        if ($status) $table = $this->prefix.$table;
        $this->join[] = [
            'table' => $table, 
            'on' => $on,
            'join' => strtoupper($join)
        ];
        return $this;
    }
    /**
     * 终结方法-查询
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否不执行
     * @return void
    */
    public function select($status=true) {
        $sql = $this->select_sql(false);
        $this->clean_up();

        if ($status && $this->debug==false) {
            return $this->Db->query($sql);
        }
        return $sql;
    }
    /**
     * 终结方法-查询-固定一条
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否不执行
     * @return void
    */
    public function find($status=true) {
        $sql = $this->select_sql(true);
        $this->clean_up();

        if ($status && $this->debug==false) {
            $res = $this->Db->query($sql);
            if ($res == false) return false;
            return array_shift($res);
        }
        return $sql;
    }
    /**
     * 终结方法-子查询构造器
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function buildSql() {
        $sql = $this->select_sql(false);
        $this->clean_up();

        return ' ( '.rtrim($sql, ';').' ) ';
    }
    /**
     * 终结方法-删除
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否不执行
     * @return void
    */
    public function delete($status=true) {
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
        $this->clean_up();
        
        if ($status && $this->debug==false) {
            return $this->Db->query($sql);
        }
        return $sql;
    }
    /**
     * 终结方法-修改
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function update($data) {
        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET ';

        foreach ($data as $key=>$val) {
            $sql .= $key.'='.$this->int_string($val).',';
        }
        $sql = rtrim($sql, ',');
        $sql = $this->where_sql($sql).';';
        
        $this->clean_up();

        if ($this->debug==false) {
            return $this->Db->query($sql);
        }
        return $sql;
    }
    /**
     * 终结方法-新增
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function insert($data) {
        $list = [];
        # 检测是否单个新增
        if (!isset($data[0])) {
            $list[] = $data;
        } else {
            $list = $data;
        }

        $sql = 'INSERT INTO';
        $sql .= ' '.$this->table;

        $field = ' (';
        $array = reset($list);
        foreach ($array as $key=>$val) {
            $field .= $key.',';
        }
        $sql .= rtrim($field, ',').')';

        $sql .= ' VALUES ';
        foreach ($list as $val) {
            $field = '(';
            foreach ($val as $v) {
                $field .= $this->int_string($v).',';
            }
            $sql .= rtrim($field, ',').'),';
        }
        
        $this->clean_up();

        if ($this->debug==false) {
            return $this->Db->query($sql);
        }
        return rtrim($sql, ',').';';
    }
    /**
     * 终结方法-新增
     * @todo 无
     * @author 小黄牛
     * @version v1.1.10 + 2020.07.17
     * @deprecated 暂不启用
     * @global 无
     * @return bool|int
    */
    public function insertGetId($data) {

        $sql = 'INSERT INTO';
        $sql .= ' '.$this->table;

        $field = ' (';
        foreach ($data as $key=>$val) {
            $field .= $key.',';
        }
        $sql .= rtrim($field, ',').')';

        $sql .= ' VALUES ';
        $field = '(';
        foreach ($data as $val) {
            $field .= $this->int_string($val).',';
        }
        $sql .= rtrim($field, ',').');';
        
        $this->clean_up();

        if ($this->debug != false) {
            return rtrim($sql, ',').';';
        }

        $res = $this->Db->query($sql);
        if (!$res) return false;

        $res = $this->Db->query('SELECT LAST_INSERT_ID() as num;');
        if (!$res) return true;
        
        return $res[0]['num'];
    }

    /**
     * 自增
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @param int $num 值
     * @return void
    */
    public function setInc($field, $num=1) {
        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET';
        $sql .= ' '.$field.'='.$field.'+'.$num;
        $sql = $this->where_sql($sql).';';
        
        $this->clean_up();

        if ($this->debug==false) {
            return $this->Db->query($sql);
        }
        return $sql;
    }
    /**
     * 自减
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @param int $num 值
     * @return void
    */
    public function setDec($field, $num=1) {
        $sql = 'UPDATE';
        $sql .= ' '.$this->table;
        $sql .= ' SET';
        $sql .= ' '.$field.'='.$field.'-'.$num;
        $sql = $this->where_sql($sql).';';
        
        $this->clean_up();
        
        if ($this->debug==false) {
            return $this->Db->query($sql);
        }
        return $sql;
    }
    /**
     * 聚合操作(统计数量)
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return mixed
    */
    public function count($field=false) {
        $field = $field ?? '*';
        $this->field = 'COUNT('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$this->ploy_alias];
    }
    /**
     * 聚合操作(获取最大值)
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return mixed
    */
    public function max($field=false) {
        if ($field == false) return false;
        $this->field = 'MAX('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$this->ploy_alias];
    }
    /**
     * 聚合操作(获取最小值)
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return mixed
    */
    public function min($field=false) {
        if ($field == false) return false;
        $this->field = 'MIN('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$this->ploy_alias];
    }
    /**
     * 聚合操作(获取平均值)
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return mixed
    */
    public function avg($field=false) {
        if ($field == false) return false;
        $this->field = 'AVG('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$this->ploy_alias];
    }
    /**
     * 聚合操作(获取总分)
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return mixed
    */
    public function sum($field=false) {
        if ($field == false) return false;
        $this->field = 'SUM('.$field.') AS '.$this->ploy_alias;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$this->ploy_alias];
    }
    /**
     * 获取某个字段的值
     * @todo 无
     * @author 小黄牛
     * @version v1.1.7 + 2020.07.16
     * @deprecated 暂不启用
     * @global 无
     * @param string $field 字段名
     * @return void
    */
    public function value($field) {
        if ($field == false) return false;
        $this->field = $field;
        $sql = $this->select_sql(true);
        $this->clean_up();

        $res = $this->Db->query($sql);
        if ($res == false) return false;
        $info = array_shift($res);

        return $info[$field];
    }
    /**
     * 执行原生SQL
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $sql 原生SQL
     * @return void
    */
    public function query($sql) {
        return $this->Db->query($sql);
    }
    /**
     * 查询相关通用语句组装
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param string $SQL sql语句
     * @return void
    */
    private function where_sql($sql) {
        if ($this->where) {
            $sql .= ' where';
            foreach ($this->where as $k=>$v) {
                if ($k == 0) {
                    $sql .= ' '.$v;
                } else {
                    $sql .= ' AND '.$v;
                }
            }
        }
        return $sql;
    }

    /**
     * 组装查询语句
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否为单条记录
     * @return void
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
            $sql .= ' LIMIT 1';
        }
        $sql .= ';';

        $this->clean_up();
        return $sql;
    }

    /**
     * 判断是否为数字类型
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $string
     * @return string
    */
    private function int_string($string) {
        if (is_numeric($string)) return $string;
        # 判断是怕查询内容里带单双引号
        if (stripos($string, '"') !== false) {
            return '\''.$this->anti($string).'\'';
        }
        return "\"".$this->anti($string)."\"";
    }

    /**
     * 转义函数
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.29
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $data 需要转义的内容
     * @return mixed
    */
    private function anti($data) {
        $list = \x\Config::run()->get('mysql.function');
        foreach ($list as $v) {
            $data = $v($data);
        }

        return $data;
    }

    /**
     * 由于是单例，用完就得清除某些共用成员
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
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
    }
}