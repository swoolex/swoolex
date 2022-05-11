<?php
/**
 * +----------------------------------------------------------------------
 * ORM构造器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\orm;
use x\elasticsearch\orm\BuilderInterface;
use x\elasticsearch\orm\BuilderAbstract;
use x\elasticsearch\tool\Client;
use Exception;

class Builder extends BuilderAbstract implements BuilderInterface 
{
    /**
     * Es实例
    */
    private $Elasticsearch;
    /**
     * Es 写入数据的超时控制
    */
    private $es_write_outtime = '5m';
    /**
     * 当前操作的索引表名
    */
    private $database;
    /**
     * 当前操作的数据表名
    */
    private $table;
    /**
     * 调试模式
    */
    private $debug = false;
    /**
     * 当前操作的主键ID
    */
    private $id = null;
    /**
     * 字段
    */
    private $field = null;
    /**
     * 记录数
    */
    private $limit;
    /**
     * 分页记录数
    */
    private $page;
    /**
     * 查询条件
    */
    private $where = [];
    /**
     * 排序条件
    */
    private $order = [];
    
    /**
     * 指定配置参数
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param Elasticsearch $Elasticsearch
     * @return this
    */
    public function __construct($Elasticsearch) {
        $this->Elasticsearch = $Elasticsearch;
        $this->es_write_outtime = \x\Config::get('elasticsearch.es_write_outtime');
    }
    
    /**
     * 选择索引表
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $database
     * @return this
    */
    public function table($database) {
        $this->database = $database;
        return $this;
    }

    /**
     * 选择数据表
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $table
     * @return this
    */
    public function name($table) {
        $this->table = $table;
        return $this;
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
     * 查询条件
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @param string $field 可以为批量表达式，也可以是字段
     * @param string $operator 表达式
     * @param string $value 查询模式
     * @return this
    */
    public function where($field, $operator=null, $value=false, $type='must') {
        if (!$field) return $this;
        // 数组
        if (is_array($field)) {
            foreach ($field as $v) {
                $v[3] = $v[3] ?? $type;
                $this->where[] = $v;
            }
        // 键值对
        } else {
            if ($value !== false) {
                $this->where[] = [$field, $operator, $value, $type];
            } else {
                $this->where[] = [$field, '=', $operator, $type];
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
            $arr = explode(' ', $order);
            $this->order[] = [
                $arr[0],
                $arr[1] ?? 'desc',
            ];
        }
        return $this;
    }

    /**
     * 开启调试
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-07
     * @return this
    */
    public function debug() {
        $this->debug = true;
        return $this;
    }

    /**
     * 创建一个索引表
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $settings 配置信息
     * @param array $field 字段信息
     * @return bool
    */
    public function createTable($settings=null, $field=null) {
        $url = '/'.$this->database;
        $json = [];
        if ($settings) $json['settings'] = $settings;
        if ($field) {
            $json['mappings']['properties'] =  $field;
        }
        $res = $this->exec($url, Client::PUT, $json);
        if ($this->debug) return $res;
        return $res['acknowledged'] ?? false;
    }
    /**
     * 删除一个索引表
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return bool
    */
    public function deleteTable() {
        $url = '/'.$this->database;
        $res = $this->exec($url, Client::DELETE);
        if ($this->debug) return $res;
        return $res['acknowledged'] ?? false;
    }

    /**
     * 读取索引表详情
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return false|array
    */
    public function getTable() {
        $url = '/'.$this->database;
        $res = $this->exec($url, Client::GET);
        if ($this->debug) return $res;
        return $res[$this->database] ?? false;
    }

    /**
     * 设置索引表别名
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $name 别名名称
     * @return bool
    */
    public function aliasTable($name) {
        $url = '/'.$this->database.'/_alias/'.$name;
        $res = $this->exec($url, Client::PUT);
        if ($this->debug) return $res;
        return $res['acknowledged'] ?? false;
    }

    /**
     * 修改索引表的字段信息
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $field 字段信息
     * @return bool
    */
    public function updateField($field) {
        $url = '/'.$this->database.'/_mapping/_doc?include_type_name=true';
        $json = [];
        $json['properties'] =  $field;
        $res = $this->exec($url, Client::PUT, $json);
        if ($this->debug) return $res;
        return $res['acknowledged'] ?? false;
    }

    /**
     * 新增数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @return false|string|int
    */
    public function insert($data) {
        $url = '/'.$this->database.'/_doc';
        
        if ($this->id) {
            $url .= '/'.$this->id.'/?op_type=create';
            $type = Client::PUT;
        } else {
            $url .= '?op_type=index';
            $type = Client::POST;
        }

        $res = $this->exec($url.'&timeout='.$this->es_write_outtime, $type, $data);
        if ($this->debug) return $res;
        return $res['_id'] ?? false;
    }

    /**
     * 新增数据[批量]
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @param int $oneMax 批量一次最大插入数据量
     * @return array
    */
    public function insertAll($data, $oneMax=1000) {
        $url = '/'.$this->database.'/_doc';
        
        $all = [];
        $i = 0;
        $num = 0;
        foreach ($data as $k=>$v) {
            if ($num == $oneMax) {
                $i++;
                $num = 0;
            }
            $arr = [
                'index'=> [
                    '_index'=>$this->database,
                ]
            ];
            if (isset($v['id'])) $arr['index']['_id'] = $v['id'];
            $json = json_encode($arr)."\n".json_encode($v)."\n";
            if (isset($all[$i])) {
                $all[$i] .= $json;
            } else {
                $all[$i] = $json;
            }
            $num++;
        }
        if ($this->debug) return $all;

        // 批量执行 - 此处可以使用多进程
        $channel = new \Swoole\Coroutine\Channel;
        $url = '/_bulk?timeout='.$this->es_write_outtime;
        foreach ($all as $k=>$json) {
            //创建子进程
            go(function () use ($channel, $url, $json){
                $res = $this->exec($url, Client::PUT, $json);
                $channel->push($res);
            });
        }
        // 获取结果
        $ret = [];
        foreach ($all as $k=>$json) {
            $res = $channel->pop();
            $ret[$k] = $res['items'] ?? false;
        }
        // 关闭管道
        $channel->close();
        
        return $ret;
    }

    /**
     * 更新数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $data 数据集
     * @return bool
    */
    public function update($data) {
        $url = '/'.$this->database.'/_doc/'.$this->id.'?timeout='.$this->es_write_outtime;
        $res = $this->exec($url, Client::PUT, $data);
        if ($this->debug) return $res;
        return !empty($res['_id']) ? true : false;
    }

    /**
     * 删除数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return bool
    */
    public function delete() {
        if ($this->id) {
            $url = '/'.$this->database.'/_doc/'.$this->id;
            $res = $this->exec($url, Client::DELETE);
            
            if ($this->debug) return $res;
            return !empty($res['_id']) ? true : false;
        } else {
            $data = $this->make_where();
            $url = '/'.$this->database.'/_delete_by_query';
            $res = $this->exec($url, Client::POST, $data);

            if ($this->debug) return $res;
            return $res['deleted'] ?? false;
        }
    }

    /**
     * 读取某条数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string|int $id 指定ID
     * @return bool
    */
    public function get($id=null) {
        $id = !empty($id) ? $id : $this->id;
        $url = '/'.$this->database.'/_doc/'.$id;
        $res = $this->exec($url, Client::GET);
        if ($this->debug) return $res;
        return $res['_source'] ?? false;
    }

    /**
     * 读取某条数据的某个值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $field 字段名
     * @return bool
    */
    public function value($field) {
        $res = $this->get();
        if ($this->debug) return $res;
        if ($res == false) return false;
        return $res[$field] ?? false;
    }

    /**
     * 查询数据
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return false|array
    */
    public function select() {
        $data = $this->make_where();
        $url = '/'.$this->database.'/_search';
        $res = $this->exec($url, Client::GET, $data);
        if ($this->debug) return $res;
        if (isset($res['failed_shards'][0]['reason']['reason'])) {
            throw new Exception($res['failed_shards'][0]['reason']['reason']);
            return false;
        }
        return $res['hits']['hits'] ?? false;
    }

    /**
     * 查询记录数
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return false|int
    */
    public function count() {
        $data = $this->make_where();
        $url = '/'.$this->database.'/_count';
        $res = $this->exec($url, Client::GET, $data);
        if ($this->debug) return $res;
        return $res['count'] ?? false;
    }
    
    /**
     * 最大值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string|array 字段
     * @return this
    */
    public function max($field) {
        if (is_array($field)) {
            foreach ($field as $v) {
                $this->aggs['max'][$v] = 1;
            }
        } else {
            $this->aggs['max'][$field] = 1;
        }
        
        return $this;
    }
    
    /**
     * 最小值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string|array 字段
     * @return this
    */
    public function min($field) {
        if (is_array($field)) {
            foreach ($field as $v) {
                $this->aggs['min'][$v] = 1;
            }
        } else {
            $this->aggs['min'][$field] = 1;
        }
        
        return $this;
    }

    /**
     * 平均值
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string|array 字段
     * @return this
    */
    public function avg($field) {
        if (is_array($field)) {
            foreach ($field as $v) {
                $this->aggs['avg'][$v] = 1;
            }
        } else {
            $this->aggs['avg'][$field] = 1;
        }
        
        return $this;
    }
    
    /**
     * 求和
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string|array 字段
     * @return this
    */
    public function sum($field) {
        if (is_array($field)) {
            foreach ($field as $v) {
                $this->aggs['sum'][$v] = 1;
            }
        } else {
            $this->aggs['sum'][$field] = 1;
        }
        
        return $this;
    }
    
    /**
     * 执行聚合查询
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return false|array
    */
    public function aggs() {
        $data = $this->make_where();
        if (empty($this->aggs)) return false;

        foreach ($this->aggs as $key => $array) {
            foreach ($array as $field => $v) {
                $lable = $key.'_'.$field;
                $data['aggs'][$lable][$key]['field'] = $field;
            }
        }

        $url = '/'.$this->database.'/_search?size=0';
        $res = $this->exec($url, Client::GET, $data);
        if ($this->debug) return $res;
        if (!isset($res['aggregations'])) return false;
        $array = [];
        foreach ($res['aggregations'] as $k=>$v) {
            $array[$k] = $v['value'];
        }
        return $array;
    }

    /**
     * 生成通用的查询结构
     * @todo 无
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-09
     * @deprecated 暂不启用
     * @global 无
     * @return array
    */
    private function make_where() {
        $json = [];
        // 返回字段
        if ($this->field) {
            $json['_source'] = $this->field;
        }
        // 分页数据
        if ($this->page) {
            if ($this->page['left'] <= 1) {
                $left = 0;
            } else {
                $left = ($this->page['left']-1) * $this->page['right'];
            }
            $json['from'] = $left;
            $json['size'] = $this->page['right'];
        } else {
            if ($this->limit) {
                $json['from'] = $this->limit['left'];
                if ($this->limit['right']) {
                    $json['size'] = $this->limit['right'];
                }
            }
        }
        // 排序
        foreach ($this->order as $v) {
            $json['sort'][$v[0]]['order'] = $v[1];
        }

        // 查询表达式
        $json['query'] = $this->parserWhere($this->where);

        return $json;
    }

    /**
     * 发送请求
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param string $url   请求地址
     * @param string $verb  动词
     * @param array $json 请求数据
     * @return mixed
    */
    public function exec($url, $verb, $json=[]) {
        return $this->Elasticsearch->exec($url, $verb, $json, [], $this->debug);
    }
}