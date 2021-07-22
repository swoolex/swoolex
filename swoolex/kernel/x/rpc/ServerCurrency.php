<?php
/**
 * +----------------------------------------------------------------------
 * 微服务-服务端通用助手类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\rpc;

class ServerCurrency
{
    /**
     * 数据返回
     * @todo 无
     * @author 小黄牛
     * @version v1.2.24 + 2021.1.9
     * @deprecated 暂不启用
     * @global 无
     * @param server $server
     * @param fd $fd
     * @param string $status
     * @param mixed $msg
     * @param array $data
     * @return void
    */
    public function returnJson($server, $fd, $status, $msg="SUCCESS", $data=[], $request=null) {
        $json = json_encode([
            'status' => "{$status}",
            'msg' => $msg,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE);

        if (\x\Config::get('rpc.aes_status') == true) {
            $Currency = new Currency();
            $json = $Currency->aes_encrypt($json);
            unset($Currency);
        }

        if ($fd == 0) {
            if ($request) {
                $txt  = '请求路由：'.$request['class'].PHP_EOL;
                $txt .= '请求方法：'.$request['function'].PHP_EOL;
                $txt .= '请求头：'.json_encode($request['headers'], JSON_UNESCAPED_UNICODE).PHP_EOL;
                $txt .= '请求参数：'.json_encode($request['param'], JSON_UNESCAPED_UNICODE).PHP_EOL;
                $txt .= '失败状态：'.$status.PHP_EOL;
                $txt .= '失败描述：'.$msg.PHP_EOL.PHP_EOL;
    
                $dir = WORKLOG_PATH.'rpc'.DS;
                if (is_dir($dir) == false) {
                    mkdir($dir, 0755);
                }
    
                $file_path = $dir.date('Ymd').'.log';
                // 写入日志记录
                \Swoole\Coroutine\System::writeFile($file_path, $txt, FILE_APPEND);
            }
            return $data;
        }
        return $server->send($fd, $json);
    }
}