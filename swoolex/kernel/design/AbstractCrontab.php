<?php
/**
 * +----------------------------------------------------------------------
 * 定时任务-抽象类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/
namespace design;

abstract class AbstractCrontab {
    /**
     * 定时器的触发规则
    */
    private $rule = '';
    /**
     * Swoole服务实例
    */
    private $server;
    /**
     * 本个定时任务的timer_id
    */
    private $timer_id;
    /**
     * 秒
    */
    protected $second;
    /**
     * 分钟
    */
    protected $minute;
    /**
     * 小时
    */
    protected $hour;
    /**
     * 天数
    */
    protected $day;
    /**
     * 月份
    */
    protected $month;
    /**
     * 星期
    */
    protected $week; 

    /**
     * 必须实现的定时器入口方法
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function run();

    /**
     * 设置定时器规则
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $rule
     * @return void
    */
    public final function setRule($rule) {
        $this->rule = $rule;
    }
    
    /**
     * 设置Swoole服务实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $server
     * @return void
    */
    public final function setServer($server) {
        $this->server = $server;
    }

    /**
     * 设置定时器任务ID
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param int $timer_id
     * @return void
    */
    public final function setTimerId($timer_id) {
        $this->timer_id = $timer_id;
    }

    /**
     * 获取定时器规则
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return mixed
    */
    protected final function get_rule() {
        return $this->rule;
    }

    /**
     * 获取Swoole服务实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return Swoole\Server
    */
    protected final function get_server() {
        return $this->server;
    }

    /**
     * 获取定时器任务ID
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return int
    */
    protected final function get_timer_id() {
        return $this->timer_id;
    }

    /**
     * 获取当前系统时间，分割
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private final function get_data_list() {
        $time = time();
        # 秒
        $this->second = $this->delete_zero(date('s', $time));
        # 分钟
        $this->minute = $this->delete_zero(date('i', $time));
        # 小时
        $this->hour = $this->delete_zero(date('H', $time));
        # 天数
        $this->day = $this->delete_zero(date('d', $time));
        # 月份
        $this->month = $this->delete_zero(date('m', $time));
        # 星期 (0就是周末)
        $this->week = date('w', $time);
    }

    /**
     * 删除开头0
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return int
    */
    private final function delete_zero($str) {
        return intval($str);
    }

    /**
     * 任务规则切割
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $rule 
     * @return int|array
    */
    public final function rule_cutting($rule) {
        // Linux风格
        if (strpos($rule," ")) {
            $rule = trim(preg_replace("/\s(?=\s)/","\\1", $rule));
            $array = explode(" ", $rule);
            // 规则不规范
            if (count($array) != 6) return false;

            return [
                'second' => $array[0],// 秒
                'minute' => $array[1],// 分钟
                'hour'   => $array[2],// 小时
                'day'    => $array[3],// 天数
                'month'  => $array[4],// 月份
                'week'   => $array[5],// 星期
            ];
        }
        // 毫秒
        return (int)$rule;
    }

    /**
     * 验证规则是否能够执行
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param array $v 规则分解
     * @return bool
    */
    public final function task_vif($v) {
        // 重置一次当前时间
        $this->get_data_list();
        // 秒级特殊规则
        if ($v['second']!='*' && $v['month']=='*' && $v['week']=='*' && $v['day']=='*' && $v['hour']=='*' && $v['minute']=='*') {
            # 每*秒运行一次
            if (is_int($this->second / $v['second']) === false) {
                return false;
            }
        // 分级特殊规则
        } else if ($v['minute']!='*' && $v['month']=='*' && $v['week']=='*' && $v['day']=='*' && $v['hour']=='*') {
            # 每*分运行一次
            if ($v['second'] != '*') {
                if ($this->second != $v['second'] || is_int($this->minute / $v['minute']) === false) {
                    return false;
                }
            } else if (is_int($this->minute / $v['minute']) === false) {
                return false;
            }
        // 普通规则开始计算
        } else {
            // 参数预设
            if ($v['month'] != '*' && $v['day'] == '*') $v['day'] = 1; // 第1天
            if ($v['hour'] == '*') $v['hour'] = 0; // 0时
            if ($v['minute'] == '*') $v['minute'] = 0; // 0分
            if ($v['second'] == '*') $v['second'] = 1; // 1秒

            # 计算月份
            if ($v['month'] != '*' && $this->month != $v['month']) {return false;}
            # 计算星期
            if ($v['week'] != '*' && $this->week != $v['week']) {return false;}
            # 计算天数
            if ($v['day'] != '*' && $this->day != $v['day']) {return false;}
            # 计算小时
            if ($v['hour'] != '*' && $this->hour != $v['hour']) {return false;}
            # 计算分钟，用除法
            if ($v['minute'] != '*' && $this->minute != $v['minute']) {return false;}
            # 计算秒，用除法
            if ($v['second'] != '*' && $this->second != $v['second']) { return false;}
        }

        return true;
    }
}