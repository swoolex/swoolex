<?php
/**
 * +----------------------------------------------------------------------
 * HTTP调试器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

class DebugGer
{
    private $js = '<script>
    var swoolex_debug_btn = document.getElementById("swoolex_debug_btn");
    var swoolex_debug_close = document.getElementById("swoolex_debug_close");
    var swoolex_debug_div = document.getElementById("swoolex_debug_div");
    swoolex_debug_btn.onclick=function(){
        swoolex_debug_btn.style.display="none";
        swoolex_debug_div.style.display="block";
    }
    swoolex_debug_close.onclick=function(){
        swoolex_debug_btn.style.display="flex";
        swoolex_debug_div.style.display="none";
    }
    
    var swoole_tab1 = document.getElementById("swoole_tab1");
    var swoole_tab2 = document.getElementById("swoole_tab2");
    var swoole_tab3 = document.getElementById("swoole_tab3");
    var swoole_tab4 = document.getElementById("swoole_tab4");
    var swoole_div1 = document.getElementById("swoole_div1");
    var swoole_div2 = document.getElementById("swoole_div2");
    var swoole_div3 = document.getElementById("swoole_div3");
    var swoole_div4 = document.getElementById("swoole_div4");
    swoole_tab1.onclick=function(){
        swoole_tab1.style.color="#fff";
        swoole_tab1.style.background="#0095ff";
        swoole_tab2.style.color="#333";
        swoole_tab2.style.background="#fff";
        swoole_tab3.style.color="#333";
        swoole_tab3.style.background="#fff";
        swoole_tab4.style.color="#333";
        swoole_tab4.style.background="#fff";
        
        swoole_div1.style.display="block";
        swoole_div2.style.display="none";
        swoole_div3.style.display="none";
        swoole_div4.style.display="none";
    }
    swoole_tab2.onclick=function(){
        swoole_tab2.style.color="#fff";
        swoole_tab2.style.background="#0095ff";
        swoole_tab1.style.color="#333";
        swoole_tab1.style.background="#fff";
        swoole_tab3.style.color="#333";
        swoole_tab3.style.background="#fff";
        swoole_tab4.style.color="#333";
        swoole_tab4.style.background="#fff";
    
        swoole_div2.style.display="block";
        swoole_div1.style.display="none";
        swoole_div3.style.display="none";
        swoole_div4.style.display="none";
    }
    swoole_tab3.onclick=function(){
        swoole_tab3.style.color="#fff";
        swoole_tab3.style.background="#0095ff";
        swoole_tab2.style.color="#333";
        swoole_tab2.style.background="#fff";
        swoole_tab1.style.color="#333";
        swoole_tab1.style.background="#fff";
        swoole_tab4.style.color="#333";
        swoole_tab4.style.background="#fff";
    
        swoole_div3.style.display="block";
        swoole_div2.style.display="none";
        swoole_div1.style.display="none";
        swoole_div4.style.display="none";
    }
    swoole_tab4.onclick=function(){
        swoole_tab4.style.color="#fff";
        swoole_tab4.style.background="#0095ff";
        swoole_tab3.style.color="#333";
        swoole_tab3.style.background="#fff";
        swoole_tab2.style.color="#333";
        swoole_tab2.style.background="#fff";
        swoole_tab1.style.color="#333";
        swoole_tab1.style.background="#fff";
    
        swoole_div4.style.display="block";
        swoole_div3.style.display="none";
        swoole_div2.style.display="none";
        swoole_div1.style.display="none";
    }
    </script>';
    private $html = '';

    /**
     * 设置头部html
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_header() {
        $this->html .= '<style>.swoole_div{width: 100%;height: 35px;line-height: 35px;padding: 0 10px;border-bottom: 1px solid #eaeaea;font-size: 14px;color: #333;text-align: left;}</style>
		<div id="swoolex_debug_btn" style="position: fixed;bottom: 0;right: 0;z-index: 999999999999;width: 180px;height: 40px;box-shadow: 1px 1px 50px rgba(0,0,0,.3);cursor: pointer;display: flex;line-height: 40px;color: #333;font-size: 15px;">
			<img src="https://www.sw-x.cn/img/lg.png" width="25" style="margin: 0 10px;">
			SwooleX 调试器
		</div>
		<div id="swoolex_debug_div" style="position: fixed;top: 0;left: 0;z-index: 999999999999;width: 100%;height: 100%;background: #fff;color: #333;display:none">
			<div style="width: 100%;height: 50px;line-height: 50px;border-bottom: 1px solid #bbb;display: flex;position: relative;">
				<div style="text-align: center;width: 100px;cursor: pointer;font-size: 16px;color: #fff;background: #0095ff;" id="swoole_tab1">基本信息</div>
				<div style="text-align: center;width: 100px;cursor: pointer;font-size: 16px;" id="swoole_tab2">框架</div>
				<div style="text-align: center;width: 100px;cursor: pointer;font-size: 16px;" id="swoole_tab3">调用栈</div>
				<div style="text-align: center;width: 100px;cursor: pointer;font-size: 16px;" id="swoole_tab4">SQL</div>
				<div style="position: absolute;right: 20px;cursor: pointer;" id="swoolex_debug_close">
					<img style="margin-top: 18px;" src="data:image/gif;base64,R0lGODlhDwAPAJEAAAAAAAMDA////wAAACH/C1hNUCBEYXRhWE1QPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS4wLWMwNjAgNjEuMTM0Nzc3LCAyMDEwLzAyLzEyLTE3OjMyOjAwICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUQxMjc1MUJCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUQxMjc1MUNCQUJDMTFFMTk0OUVGRjc3QzU4RURFNkEiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRDEyNzUxOUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRDEyNzUxQUJBQkMxMUUxOTQ5RUZGNzdDNThFREU2QSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PgH//v38+/r5+Pf29fTz8vHw7+7t7Ovq6ejn5uXk4+Lh4N/e3dzb2tnY19bV1NPS0dDPzs3My8rJyMfGxcTDwsHAv769vLu6ubi3trW0s7KxsK+urayrqqmop6alpKOioaCfnp2cm5qZmJeWlZSTkpGQj46NjIuKiYiHhoWEg4KBgH9+fXx7enl4d3Z1dHNycXBvbm1sa2ppaGdmZWRjYmFgX15dXFtaWVhXVlVUU1JRUE9OTUxLSklIR0ZFRENCQUA/Pj08Ozo5ODc2NTQzMjEwLy4tLCsqKSgnJiUkIyIhIB8eHRwbGhkYFxYVFBMSERAPDg0MCwoJCAcGBQQDAgEAACH5BAAAAAAALAAAAAAPAA8AAAIdjI6JZqotoJPR1fnsgRR3C2jZl3Ai9aWZZooV+RQAOw==">
				</div>
			</div>';
    }
    /**
     * 设置尾部html
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_footer() {
        $this->html .= '</div>';
        $this->html .= $this->js;
    }
    /**
     * 基本信息
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_tab1() {
        $http_start_time = \x\Container::get('http_start_time');
        $http_end_time = microtime(true);
        $time = $http_end_time-$http_start_time;
        $upu = (memory_get_usage() - \x\Container::get('http_start_cpu')) / 1024;

        $this->html .= '<div style="width: 100%;height: 100%;overflow-y: auto;display: block;" id="swoole_div1">';
        $this->html .= '<div class="swoole_div">开始时间：'.date('Y-m-d H:i:s', $http_start_time).'</div>';
        $this->html .= '<div class="swoole_div">响应时间：'.date('Y-m-d H:i:s', $http_end_time).'</div>';
        $this->html .= '<div class="swoole_div">实际耗时：'.number_format($time, 7).'s</div>';
        $this->html .= '<div class="swoole_div">吞吐率：'.number_format(1/number_format($time, 6), 2). ' req/s</div>';
        $this->html .= '<div class="swoole_div">内存消耗：'.number_format($upu, 3).'kb</div>';
        $this->html .= '<div class="swoole_div">框架文件数：'.count($this->included).'</div>';
        $this->html .= '<div class="swoole_div">请求调用栈文件数：'.count($this->backtrace).'</div>';
        $this->html .= '<div class="swoole_div">操作系统：'.php_uname('s').'</div>';
        $this->html .= '<div class="swoole_div">PHP版本：'.PHP_VERSION.'</div>';
        $this->html .= '<div class="swoole_div">Swoole扩展版本：'.swoole_version().'</div>';
        $this->html .= '<div class="swoole_div">SW-X框架版本：'.VERSION.'</div>';
        $this->html .= '</div>';
    }
    /**
     * 框架-调用栈
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_tab2() {
        $this->html .= '<div style="width: 100%;height: 100%;overflow-y: auto;display: none;" id="swoole_div2">';
        foreach ($this->included as $val) {
            $this->html .= '<div class="swoole_div">'.str_replace(ROOT_PATH, '', $val).' ('.number_format(filesize($val)/1024, 3).'KB)</div>';
        }
        $this->html .= '</div>';

    }
    /**
     * 请求-调用栈
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_tab3() {
        $this->html .= '<div style="width: 100%;height: 100%;overflow-y: auto;display: none;" id="swoole_div3">';
        foreach ($this->backtrace as $val) {
            if (isset($val['file'])) {
                $this->html .= '<div class="swoole_div">'.str_replace(ROOT_PATH, '', $val['file']).' ('.number_format(filesize($val['file'])/1024, 3).'KB) Function：<font color="red">'.$val['function'].'</font></div>';
            } else {
                $this->html .= '<div class="swoole_div">命名空间加载：<font color="red">'.$val['class'].' -> '.$val['function'].'</font></div>';
            }
        }
        $this->html .= '</div>';
    }
    /**
     * SQL
     * @todo 无
     * @author 小黄牛
     * @version v1.2.16 + 2020.10.27
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function set_tab4() {
        $this->html .= '<div style="width: 100%;height: 100%;overflow-y: auto;display: none;" id="swoole_div4">';
        $array = \x\Container::get('http_sql_log');
        if ($array) {
            foreach ($array as $val) {
                $this->html .= '<div class="swoole_div" style="height: auto;line-height: 20px;">';
                $this->html .= '<font style="font-weight: bold;">调用来源：</font><font color="#8c2a07">'.$val['file'].'</font><br/>';
                $this->html .= '<font style="font-weight: bold;">SQL：</font><font color="red">'.$val['sql'].'</font><br/>';
                $this->html .= '<font style="font-weight: bold;">耗时：</font><font color="#b800d8">'.$val['time'].'s</font>';
                $this->html .= '</div>';
            }
        }
        $this->html .= '</div>';
    }
    /**
     * 调用
     * @todo 无
     * @author 小黄牛
     * @version v2.0.3 + 2021.03.11
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function run() {
        if (!\x\Config::get('app.de_bug')) {
            return '';
        }
        # 获得文件加载数
        $this->included = get_included_files();
        $this->backtrace = array_reverse(debug_backtrace());
        $this->set_header();
        $this->set_tab1();
        $this->set_tab2();
        $this->set_tab3();
        $this->set_tab4();
        $this->set_footer();
        # 释放内存
        unset($this->included);
        unset($this->backtrace);
        return $this->html;
    }
}