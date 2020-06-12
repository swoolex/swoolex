<?php
// +----------------------------------------------------------------------
// | 监听进程启动事件
// +----------------------------------------------------------------------
// | Copyright (c) 2018 https://blog.junphp.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小黄牛 <1731223728@qq.com>
// +----------------------------------------------------------------------

namespace event\http;

class onStart
{
    /**
	 * 启动实例
	*/
    public $server;
    /**
     * 定时器启动状态
    */
    public $status = true;
    
    /**
     * 统一回调入口
     * @todo 无
     * @author 小黄牛
     * @version v1.0.1 + 2020.05.26
     * @deprecated 暂不启用
     * @global 无
     * @param Swoole\Server $server
     * @return void
    */
    public function run($server) {
        $this->server = $server;

        // 自动载入所有定时任务
        if ($this->status) {
            $this->status = false;
            $path = ROOT_PATH.'/app/crontab/';
            $filename = scandir($path);
            foreach($filename as $k=>$v){
                if ($v=="." || $v=="..") continue;
                // 载入定时器
                $class = '\app\crontab\\'.substr($v,0,strpos($v,"."));
                $obj = new $class();
                $obj->run($server);
            }
        }

        \x\StartEo::run(\x\Lang::run()->get('start -15'));
        
        // 调用二次转发，不做重载
        $on = new \app\event\http\onStart;
        $on->run($server);
    }

}

