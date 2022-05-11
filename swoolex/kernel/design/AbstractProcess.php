<?php
/**
 * +----------------------------------------------------------------------
 * 自定义进程-基类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace design;
use Swoole\Process;
use Swoole\Event;

abstract class AbstractProcess
{
    /**
     * 进程名称
     */
    public $name = 'diy-process';
    
    /**
     * 是否需要while(true) 永久堵塞
    */
    public $onWhile = false;

    /**
     * 等待间隔时间(毫秒)  0不堵塞
    */
    public $sleepS = 0;

    /**
     * 重定向自定义进程的标准输入和输出
     */
    public $redirectStdinStdout = false;

    /**
     * 管道类型
     */
    public $pipeType = 2;

    /**
     * 是否启用协程
     */
    public $enableCoroutine = true;

    /**
     * 当前子进程实例
    */
    private $process;
    /**
     * 当前父进程实例
    */
    private $SwooleProcess;
    /**
     * 当前子进程ID
    */
    private $pid;

    /**
     * 进程业务入口方法
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
    */
    abstract public function run();

    /**
     * 业务入口执行前的前置方法
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @return true
    */
    public function front() {
        return true;
    }

    /**
     * 当进程接收到 SIGTERM 信号触发该回调
     * @author 小黄牛
     * @version v2.5.8 + 2021-10-21
    */
    public function onSigTerm() {

    }

    /**
     * 业务进程发生异常时回调
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @param Throwable $throwable
    */
    public function onException(\Throwable $throwable) 
    {
        // $throwable->getMessage(); 错误内容
        // $throwable->getFile(); 错误文件
        // $throwable->getLine(); 错误行数
    }
    
    /**
     * 接收管道数据
     * @author 小黄牛
     * @version v2.5.8 + 2021-10-21
     * @param string $process
    */
    public function read($process) {
        $data = $process->read();
    }
    
    /**
     * 启动进程
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @return Swoole\Process
    */
    public final function start() 
    {
        // 创建进程
        $this->SwooleProcess = new Process([$this, '_callback'], $this->redirectStdinStdout, $this->pipeType, $this->enableCoroutine);
        // 设置进程别名
        $this->SwooleProcess->name($this->name);

        return $this->SwooleProcess;
    }

    /**
     * 进程逻辑处理
     * @author 小黄牛
     * @version v2.5.8 + 2021-10-21
     * @param Process $process
    */
    public final function _callback(Process $process){
        // 设置子进程实例 - id
        $this->process = $process;
        $this->pid = $process->pid;
        // 监听数据投递
        Event::add($this->SwooleProcess->pipe, function(){
            try {
                $this->read($this->SwooleProcess);
            } catch (\Throwable $throwable){
                $this->onException($throwable);
            }
        });
        // 信号监听
        Process::signal(SIGTERM, function () use ($process) {
            Event::del($process->pipe);
            try {
                $this->onSigTerm();
            } catch (\Throwable $throwable){
                $this->onException($throwable);
            } finally {
                Process::signal(SIGTERM, null);
                Event::exit();
            }
        });
        try{
            // 先执行前置业务
            $status = $this->front();
            if ($status) {
                // 执行业务代码
                if ($this->onWhile == true) {
                    while (true) {
                        $this->run();
                        if ($this->sleepS) {
                            usleep($this->sleepS * 1000);
                        }
                    }
                } else {
                    $this->run();
                    if ($this->sleepS) {
                        usleep($this->sleepS * 1000);
                    }
                }
            }
        } catch (\Throwable $throwable) {
            $this->onException($throwable);
        }
        
        Event::wait();
    }

    /**
     * 获取进程名称
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @return string
    */
    public final function getProcessName() {
        return $this->name;
    }

    /**
     * 获取进程实例
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @return Swoole\Process
    */
    public final function getProcess() {
        return $this->process;
    }

    /**
     * 获取当前进程Pid
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @return int
    */
    public final function getPid(){
        return $this->pid;
    }

    /**
     * 向当前子进程传递数据
     * @author 小黄牛
     * @version v2.5.8 + 2021-09-29
     * @param mixed $mixed 数据包
     * @return bool
    */
    public final static function write($mixed) {
        $process_key = get_called_class();
        return \x\common\Process::write($process_key, $mixed);
    }
}