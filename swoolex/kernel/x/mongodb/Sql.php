<?php
/**
 * +----------------------------------------------------------------------
 * MongoDb-SQL-ORM-构造器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\mongodb;

class Sql{
    /**
     * 更新采集器
    */
    private $WriteBulk;
    /**
     * 库名
    */
    private $library = 'test';
    /**
     * 表名
    */
    private $table;
    /**
     * 条件集(通用)
    */
    private $where = [];
    /**
     * 排序
    */
    private $order = [];
    /**
     * 跳过指定数量的文档
    */
    private $skip = 0;
    /**
     * 只返回指定数量的文档
    */
    private $limit = 0;
    /**
     * 分组查询
    */
    private $group = [];
    /**
     * 返回字段
    */
    private $field = [];


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
     * 选择库
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @param string $table
     * @return this
    */
    public function table($library) {
        $this->clean_up();
        $this->library = $library;
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
        $this->table = $table;
        return $this;
    }

    /**
     * 查询条件
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $field 可以为批量表达式，也可以是字段
     * @param string $operator 表达式
     * @param string $value 条件
     * @return this
    */
    public function where($field, $operator=null, $value=false) {
        if (!$field) return $this;
        if (is_array($field)) {
            foreach ($field as $v) {
                $length = count($v);
                if ($length == 3) {
                    $this->where[$v[0]][$this->logical_operator($v[1])] = $v[2];
                } else {
                    $this->where[$v[0]][$this->logical_operator('=')] = $v[1];
                }
            }
        } else {
            if ($value !== false) {
                $this->where[$field][$this->logical_operator($operator)] = $value;
            } else {
                $this->where[$field][$this->logical_operator('=')] = $operator;
            }
        }

        return $this;
    }

    /**
     * In查询条件
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string|array $in
     * @return this
    */
    public function whereIn($key, $in) {
        if (!is_array($in)) {
            $in = explode(',', $in);
        }
            
        $this->where[$key]['$in'] = $in;
        return $this;
    }
    /**
     * NotIn查询条件
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string|array $in
     * @return this
    */
    public function whereNotIn($key, $in) {
        if (!is_array($in)) {
            $in = explode(',', $in);
        }
            
        $this->where[$key]['$nin'] = $in;
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
        $namespace = '\\x\mongodb\query\\'.$where.'::run';
        $ret = [];

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
            case 'notbetween': $ret = $namespace($field, $where, $data); break; // or操作
            default:
                if (!is_numeric($data)) $data = strtotime($data)*1000;
                $ret = [
                    [
                        'field' => $field,
                        'where' => $where,
                        'value' => $data,
                    ],
                ];
            break;
        }
        
        if ($ret) {
            if ($where == 'notbetween') {
                foreach ($ret as $v) {
                    $this->where['$or'][$v['field']][$this->logical_operator($v['where'])] = $v['value'];
                }
            } else {
                foreach ($ret as $v) {
                    $this->where[$v['field']][$this->logical_operator($v['where'])] = $v['value'];
                }
            }
        }

        return $this;
    } 

    /**
     * 限制数量
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $skip 跳过多少条
     * @param string $limit 返回多少条
     * @return this
    */
    public function limit($skip, $limit=null) {
        if (is_null($limit)) {
            $this->limit = $skip;
        } else {
            $this->skip = $skip;
            $this->limit = $limit;
        }
        return $this;
    }

    /**
     * 分页
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $page 页数
     * @param string $limit 返回多少条
     * @return this
    */
    public function page($page, $limit=10) {
        $skip = 0;
        if ($page > 1) {
            $skip = ($page-1) * $limit;
        }
        $this->limit = $limit;
        $this->skip = $skip;
        return $this;
    }

    /**
     * 排序
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string 排序
     * @return this
    */
    public function order($string) {
        $string = trim(preg_replace("/\s(?=\s)/","\\1", $string));
        $array =explode(',', $string);
        foreach ($array as $v) {
            list($key, $value) = explode(' ', trim($v));
            if (strtolower($value) == 'asc') {
                $this->order[$key] = 1;
            } else {
                $this->order[$key] = -1;
            }
        }
        return $this;
    }
    
    /**
     * 限制返回的字段
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $string
     * @return this
    */
    public function field($string) {
        $string = trim(preg_replace("/\s(?=\s)/","\\1", $string));
        $array =explode(',', $string);
        foreach ($array as $key) {
            $this->field[$key] = 1;
        }
        return $this;
    }

    /**
     * 限制不返回的字段
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $string
     * @return this
    */
    public function fieldOn($string) {
        $string = trim(preg_replace("/\s(?=\s)/","\\1", $string));
        $array =explode(',', $string);
        foreach ($array as $key) {
            $this->field[$key] = 0;
        }
        return $this;
    }

    /**
     * 分组条件
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-31
     * @param string $string
     * @return this
    */
    public function group($string) {
        $array = explode(',', $string);
        foreach ($array as $key) {
            $this->group['_id'][$key] = '$'.$key;
        }
        return $this;
    }

    /**
     * 单条新增
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param array $data
     * @return bool
    */
    public function insert($data) {
        $this->WriteBulk->insert($data);
        return $this->write();
    }
    
    /**
     * 批量新增
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param array $array
     * @return bool
    */
    public function insertAll($array) {
        foreach ($array as $data) {
            $this->WriteBulk->insert($data);
        }
        return $this->write();
    }
    
    /**
     * 更新
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param array $data
     * @return bool
    */
    public function update($data, $config=[]) {
        if (!$this->where) return false;

        $set = ['$set' => $data];
        $extend = array_merge(['multi' => false, 'upsert' => false], $config);
        $this->WriteBulk->update($this->where, $set, $extend);
        return $this->write();
    }
    
    /**
     * 自增
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string|array $fields
     * @return bool
    */
    public function setInc($fields, $num=1, $config=[]) {
        if (!$this->where) return false;
        
        $data = [];
        if (is_string($fields)) {
            $data['$inc'][$fields] = $num;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $data['$inc'][$field] = $value;
            }
        }

        $extend = array_merge(['multi' => true, 'upsert' => false], $config);
        $this->WriteBulk->update($this->where, $data, $extend);

        return $this->write();
    }

    /**
     * 自减少
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string|array $fields
     * @return bool
    */
    public function setDec($fields, $num=1, $config=[]) {
        if (!$this->where) return false;

        $data = [];
        if (is_string($fields)) {
            $data['$inc'][$fields] = 0-$num;
        } elseif (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $data['$inc'][$field] = 0-$value;
            }
        }
        
        $extend = array_merge(['multi' => true, 'upsert' => false], $config);
        $this->WriteBulk->update($this->where, $data, $extend);

        return $this->write();
    }

    /**
     * 删除
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool
    */
    public function delete($config=[]) {
        if (!$this->where) return false;

        $extend = array_merge(['limit' => false], $config);
        $where = $this->where;
        $this->WriteBulk->delete($this->where, $extend);

        return $this->write();
    }

    /**
     * 查询多条
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|array
    */
    public function select() {
        $where = $this->select_sql();
        $QueryBulk = new \MongoDB\Driver\Query($where['where'], $where['options']);
        $cursor = $this->query($QueryBulk);
        $list = [];
        foreach ($cursor as $document) {
            $bson = \MongoDB\BSON\fromPHP($document);
            $list[] = json_decode(\MongoDB\BSON\toJSON($bson), true);
        }
        return $list;
    }

    /**
     * 查询单条
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $_id 可以指定KEY-ID
     * @return false|array
    */
    public function find($_id=null) {
        if ($_id != null) {
            $this->where('_id', new \MongoDB\BSON\ObjectID($_id));
        }
        $this->limit(0, 1);
        $where = $this->select_sql();
        $QueryBulk = new \MongoDB\Driver\Query($where['where'], $where['options']);
        $cursor = $this->query($QueryBulk);
        $list = [];
        foreach ($cursor as $document) {
            $bson = \MongoDB\BSON\fromPHP($document);
            $list = json_decode(\MongoDB\BSON\toJSON($bson), true);
        }
        return $list;
    }

    /**
     * 执行通用命令
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $commands
     * @return bool|array
    */
    public function command($commands) {
        try {
            $cursor = $this->Db->Mongo->executeCommand($this->library, new \MongoDB\Driver\Command($commands));
            $this->clean_up();
            return $cursor;
        } catch (\Exception $e) {
            $this->clean_up();
            throw new \Exception("MongoDb Command Error ".$e->getMessage());
            return false;
        }
        return false;
    }

    /**
     * 执行更多聚合命令
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $commands
     * @return bool|array
    */
    public function aggregate($commands) {
        $commands = [
            'aggregate' => $this->table,
            'pipeline' => $commands,
            'cursor' => new \stdClass
        ];
        $cursor = $this->command($commands);
        if (!$cursor) return false;
        $response = $cursor->toArray();
        return $response;
    }

    /**
     * 获取集合中指定字段的不重复值
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-31
     * @param string $key 字段
     * @return array
    */
    public function distinct($key) {
        $data = [
            'distinct' => $this->table,
            'key' => $key,
        ];
        if ($this->where) $data['query'] = $this->where;
        $cursor = $this->command($data);
        if (!$cursor) return false;
        $response = current($cursor->toArray())->values;
        return $response;
    }

    /**
     * 聚合查询，全部条数
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|int
    */
    public function count() {
        if (!$this->where) return false;
        
        $commands = [
            "count" => $this->table,
            "query" => $this->where
        ];
        $cursor = $this->command($commands);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->n;
    }

    /**
     * 聚合查询，汇总
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|int|float
    */
    public function sum($field) {
        $commands = $this->pipeline_sql($field, 'sum');
        $response = $this->aggregate($commands);
        return $this->pipeline_return($response, $field);
    }

    /**
     * 聚合查询，平均
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|int|float
    */
    public function avg($field) {
        $commands = $this->pipeline_sql($field, 'avg');
        $response = $this->aggregate($commands);
        return $this->pipeline_return($response, $field);
    }

    /**
     * 聚合查询，最小
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|int|float
    */
    public function min($field) {
        $commands = $this->pipeline_sql($field, 'min');
        $response = $this->aggregate($commands);
        return $this->pipeline_return($response, $field);
    }

    /**
     * 聚合查询，最大
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return bool|int|float
    */
    public function max($field) {
        $commands = $this->pipeline_sql($field, 'max');
        $response = $this->aggregate($commands);
        return $this->pipeline_return($response, $field);
    }

    /**
     * 删除整个库
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @return bool
    */
    public function dropDatabase() {
        $commands = [
            'dropDatabase' => 1,
        ];
        $cursor = $this->command($commands);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->ok ? true : false;
    }

    /**
     * 删除整个表
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @return bool
    */
    public function dropTable() {
        $commands = [
            'drop' => $this->table,
        ];
        $cursor = $this->command($commands);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->ok ? true : false;
    }

    /**
     * 添加索引
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string|array $key 字段名称
     * @param string $name 索引类型
     * @return bool
    */
    public function createIndex($key, $name = 'index') {
        $cmd = [
            'createIndexes' => $this->table,
            'indexes' => [],
        ];
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $cmd['indexes'][] = [
                    'name' => $v,
                    'key' => [$k=>1],
                ];  
            }
        } else {
            $cmd['indexes'][] = [
                'name' => $name,
                'key' => [$key=>1],
            ];
        }
        $cursor = $this->command($cmd);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->ok ? true : false;
    }

    /**
     * 删除全部索引
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @return bool
    */
    public function removeIndex() {
        $commands = [
            'dropIndexes' => $this->table,
        ];
        $cursor = $this->command( $commands);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->ok ? true : false;
    }

    /**
     * 删除某个索引
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @param string|array $key 字段名称
     * @return bool
    */
    public function dropIndex($key) {
        $cmd = [
            'dropIndexes' => $this->table,
            'indexes' => []
        ];
        if (is_array($key)) {
            foreach ($key as $v) {
                $cmd['indexes'][] = [
                    'key' => $v,
                ];  
            }
        } else {
            $cmd['indexes'][] = [
                'key' => $key,
            ];
        }
        $cursor = $this->command($cmd);
        if (!$cursor) return false;
        $response = current($cursor->toArray());
        return $response->ok ? true : false;
    }

    /**
     * 查看全部索引
     * @author 小黄牛
     * @version v2.5.4 + 2021-09-01
     * @return array
    */
    public function listIndexe() {
        $commands = [
            'listIndexes' => $this->table,
        ];
        $cursor = $this->command($commands);
        if (!$cursor) return false;
        $array = $cursor->toArray();
        $list = [];
        
        foreach ($array as $v) {
            $val = (array)$v;
            $field = (array)$val['key'];
            $list[] = [
                'field' => key($field),
                'type' => $val['name'],
            ];
        }

        return $list;
    }

    /**
     * 获取普通查询条件集
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return array
    */
    private function select_sql() {
        $where = $this->where;
        $options = [];
        if ($this->field) $options['projection'] = $this->field;
        if ($this->group) $options['group'] = $this->group;
        if ($this->order) $options['sort'] = $this->order;
        if ($this->skip) $options['skip'] = $this->skip;
        if ($this->limit) $options['limit'] = $this->limit;
        
        return [
            'where' => $where,
            'options' => $options,
        ];
    }

    /**
     * 获取聚合查询条件集
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return array
    */
    private function pipeline_sql($field, $type) {
        $commands = [];

        if ($this->where) $commands[]['$match'] = $this->where;
        if ($this->group) {
            $commands[]['$group'] = [
                '_id' => current($this->group),
                $field => [
                    '$'.$type => '$'.$field,
                ],
            ];
        } else {
            $commands[]['$group'] = [
                '_id' => null,
                $field => [
                    '$'.$type => '$'.$field,
                ],
            ];
        }
        if ($this->skip) $commands[]['$skip'] = $this->skip;
        if ($this->limit) $commands[]['$limit'] = $this->limit;
        if ($this->order) $commands[]['$sort'] = $this->order;
        return $commands;
    }

    /**
     * 聚合查询结果集返回处理
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @return string
    */
    private function pipeline_return($response, $field) {
        if (count($response) > 1) {
            $list = [];
            $i = 0;
            foreach ($response as $val) {
                $array = (array)$val->_id;
                $status = true;
                foreach ($array as $k => $v) {
                    if (is_null($v)) {
                        $status = false;
                        break;
                    }
                    $list[$i][$k] = $v;
                }
                if (!$status) {
                    unset($list[$i]);
                    continue;
                }
                $list[$i][$field] = $val->$field;
                $i++;
            }
            return $list;
        } else {
            $obj = current($response);
            return $obj->$field;
        }
    }

    /**
     * 由于是单例，用完就得清除某些共用成员
     * @author 小黄牛
     * @version v1.2.17 + 2020.10.29
    */
    private function clean_up() {
        $this->WriteBulk = new \MongoDB\Driver\BulkWrite();
        // $this->library = 'test';
        // $this->table = null;
        $this->where = [];
        $this->order = [];
        $this->skip = 0;
        $this->limit = 0;
        $this->field = [];
        $this->group = [];
    }

    /**
     * 运算符转换
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
     * @param string $operator
     * @return false|string
    */
    private function logical_operator($operator) {
        if ($operator == '=') return '$eq';
        if ($operator == '>') return '$gt';
        if ($operator == '<') return '$lt';
        if ($operator == '>=') return '$gte';
        if ($operator == '<=') return '$lte';
        if ($operator == '!=') return '$ne';
        if ($operator == 'like') return '$regex';
        
        return false;
    }

    /**
     * 执行更新操作
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
    */
    private function write() {
        try {
            $res = $this->Db->Mongo->executeBulkWrite($this->library.'.'.$this->table, $this->WriteBulk);
            $this->clean_up();
            // 返回结果数
            return true;
            // 正常应该用下面3个方法，但测试一直返回0，但执行却成功
            // return $res->getInsertedCount(); // 新增
            // return $res->getModifiedCount(); // 修改
            // return $res->getDeletedCount(); // 删除
        } catch (\MongoDB\Driver\Exception\BulkWriteException $e) {
            $result = $e->getWriteResult();
            $writeConcernError = $result->getWriteConcernError();
            throw new \Exception("MongoDb Write Error ".$writeConcernError->getMessage());
            return false;
        }
    }
    
    /**
     * 执行查询操作
     * @author 小黄牛
     * @version v2.5.4 + 2021-08-30
    */
    private function query($QueryBulk) {
        $res = $this->Db->Mongo->executeQuery($this->library.'.'.$this->table, $QueryBulk);
        $this->clean_up();

        return $res;
    }
}