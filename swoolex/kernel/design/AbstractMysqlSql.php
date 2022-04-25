<?php
/**
 * +----------------------------------------------------------------------
 * Mysql-SQL-ORM语句构造器 - 抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;

abstract class AbstractMysqlSql {
    /**
     * Db实例
    */
    public $Db;
    /**
     * 统计的别名
    */
    public $ploy_alias = 'swoolex';
    /**
     * 缓存状态
    */
    public $cache_status = false;
    /**
     * 缓存标识前缀
    */
    public $cache_prefix = 'DB_';
    /**
     * 缓存标识符
    */
    public $cache_key = null;
    /**
     * 缓存有效期(S)
    */
    public $expire_time = 3600;
    
    /**
     * 注入Db
    */
    abstract public function __construct($Db);
    /**
     * 销毁Db
    */
    abstract public function __destruct();
    /**
     * 调试SQL语句
    */
    abstract public function debug();
    /**
     * 选择表
    */
    abstract public function name($table);
    /**
     * 选择表（不带前缀）
    */
    abstract public function table($table);
    /**
     * 别名
    */
    abstract public function alias($as);
    /**
     * 条件
    */
    abstract public function where($field, $operator=null, $value=false);
    /**
     * 条件IN
    */
    abstract public function whereIn($field, $in);
    /**
     * 条件NotIn
    */
    abstract public function whereNotIn($field, $in);
    /**
     * 条件OR
    */
    abstract public function whereOr($field, $operator=null, $value=false);
    /**
     * 时间条件
    */
    abstract public function whereTime($field, $where, $data=null);
    /**
     * 打印字段
    */
    abstract public function field($field);
    /**
     * 指定条数
    */
    abstract public function limit($left, $right=null);
    /**
     * 分页条数
    */
    abstract public function page($left, $right);
    /**
     * 排序
    */
    abstract public function order($order);
    /**
     * 筛选
    */
    abstract public function having($field);
    /**
     * 分组
    */
    abstract public function group($field);
    /**
     * 测试用例别名设置
    */
    abstract public function test($name);
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
    abstract public function join($table, $on, $join='LEFT', $status=true);
    /**
     * 缓存组件
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param string $key 缓存标识
     * @return void
    */
    abstract public function cache($key=null);

    /**
     * 单独设置缓存有效期
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param mixed $expire_time 过期时间，0为永久
     * @return void
    */
    abstract public function expire($expire_time);

    /**
     * 终结方法-分页查询
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param int $size 每页数
     * @param array $query 分页配置参数
     * @return void
    */
    abstract public function paginate($size, $query=null);
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
    abstract public function select($status=true);
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
    abstract public function find($status=true);
    /**
     * 终结方法-子查询构造器
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function buildSql();
    /**
     * 终结方法-删除
     * @todo 无
     * @author 小黄牛
     * @version v1.2.2 + 2020.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否不执行
     * @return void
    */
    abstract public function delete($status=true);
    /**
     * 终结方法-修改
     * @todo 无
     * @author 小黄牛
     * @version v1.2.2 + 2020.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function update($data);
    /**
     * 终结方法-新增
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.28
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function insert($data);

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
    abstract public function setInc($field, $num=1);
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
    abstract public function setDec($field, $num=1);
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
    abstract public function count($field=false);
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
    abstract public function max($field=false);
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
    abstract public function min($field=false);
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
    abstract public function avg($field=false);
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
    abstract public function sum($field=false);
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
    abstract public function value($field);
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
    abstract public function query($sql, $status=false);
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
    abstract public function exec($sql);

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
    protected function int_string($string) {
        if (is_null($string)) return 'null';
        
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
    protected function anti($data) {
        $list = \x\Config::run()->get('mysql.function');
        foreach ($list as $v) {
            $data = $v($data);
        }

        return $data;
    }
    
    /**
     * 单元测试DB替换
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected function testcase() {
        if (\x\context\Container::get('testcase')) {
            $obj = \x\context\Container::get('testcase');
            $name = $this->test_case;
            if (isset($obj->$name)) {
                return $obj->$name;
            } else {
                return \design\Lifecycle::testcase_callback('Db-TestCase Key：'.$name.' 未定义');
            }
        }
        return 'SwooleXTestCase';
    }

    /**
     * 记录Log
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    protected function record($sql, $start_time, $end_time) {
        // 注入调试内容
        if (\x\Config::run()->get('app.sql_log_status')) {
            $debug = debug_backtrace();
            $file = '';
            // 获得调用来源
            if (!empty($debug[1])) {
                $file = !empty($debug[1]['file']) ? 'Class：'.$debug[1]['file'] : 'Function：'.$debug[1]['function'];
            }
            // 不允许记录的目录，防止上下文溢出
            $no_list = [
                '/crontab/',
                '/queue/',
                '/event/',
                '/process/',
            ];
            // 跳过记录
            foreach ($no_list as $route) {
                if (stripos($file, $route) !== false) {
                    return false;
                }
            }
            // 计算调用时间
            $time = number_format(($end_time-$start_time), 7);
            // 写入记录
            $array = \x\context\Container::get('http_sql_log');
            if (!$array) $array = [];
            $array[] = [
                'sql' => $sql,
                'file' => $file,
                'time' => $time
            ];
            \x\context\Container::set('http_sql_log', $array);
        }
    }

    /**
     * 读取缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param string $sql SQL语句
     * @return void
    */
    protected function select_cache($sql=null) {
        if ($this->cache_status == false) return ['status'=>false];
        $key = $this->cache_prefix;
        if ($this->cache_key) {
            $key .= $this->cache_key;
        } else {
            $key .= md5($sql);
        }
        $Redis = new \x\Redis();
        $res = $Redis->get($key);
        $Redis->return();

        if (!$res) return ['status'=>false];

        return [
            'status' => true,
            'data' => $res,
        ];
    }

    /**
     * 更新缓存
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param string $sql SQL语句
     * @param mixed $data 缓存内容
     * @return void
    */
    protected function create_cache($sql, $data) {
        if ($this->cache_status == false) return false;
        
        $key = $this->cache_prefix;
        if ($this->cache_key) {
            $key .= $this->cache_key;
        } else {
            $key .= md5($sql);
        }

        if (is_array($data)) $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        $Redis = new \x\Redis();
        $Redis->set($key, $data);
        if ($this->expire_time) {
            $Redis->expire($key, $this->expire_time);
        } 
        $Redis->return();
        
        // 清空缓存标识
        $this->cache_status = false;
        $this->cache_key = null;
        $this->expire_time = 3600;
        return true;
    }
}