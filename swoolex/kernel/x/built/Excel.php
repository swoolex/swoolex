<?php
/**
 * +----------------------------------------------------------------------
 * Excel常用操作
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\built;

class Excel
{
    // ---------------------- 读取 ----------------------
    /**
     * 读取Excel文件内容
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param string $file 文件地址
     * @return array
    */
    public static function open($file) {
        if (file_exists($file) == false) return false;

        $type = pathinfo($file);
        $type = strtolower($type["extension"]);

        switch ($type) {
            case 'csv':
                return self::open_csv($file);
            break;
            default :
                return self::open_xls($file);
            break;
        }    
        return false;
    }
    // 读取csv
    private static function open_csv($file) {
        $handle = fopen($file, "rb" );
        if (!$handle) return false;

        $data = [];
        while (!feof($handle)) {
            $arr = fgetcsv($handle);
            if ($arr) {
                foreach ($arr as $k => $v) {
                    $arr[$k] = $v;
                }
                $data[] = $arr;
            }
        }
        fclose($handle);
        return $data;
    }
    // 读取xls
    private static function open_xls($file) {
        $handle = fopen($file, 'rb');
        if (!$handle) return false;

        //内容读取
        $data = [];
        $count = 0;
        while (!feof($handle)) {
            $row = fgets($handle);
            $row = explode("\t", $row);
            if(!$row[0]) continue;//去除最后一行
            foreach ($row as $k => $v) {
                $row[$k] = $v;
            }
            $data[$count] = $row;
            $count++;
        }
        fclose($handle);
        return $data;
    }

    // ---------------------- 导出 ----------------------
    /**
     * 二维数组导出文件内容
     * @todo 无
     * @author 小黄牛
     * @version v2.5.25 + 2022-04-14
     * @deprecated 暂不启用
     * @global 无
     * @param array $title
     * @param array $list
     * @param string $type csv|xls  二选一
     * @param string|false $save 是否保存到本地  false.不保存  字符串时为文件保存路径
     * @param bool $dow 是否抛出浏览器下载(HTTP服务时有效)
     * @return void
    */
    public static function export($title, $list, $type='csv', $save=false, $dow=false) {
        // 标题为空时，用字段名
        if (empty($title)) {
            foreach ($list as $v) {
                foreach ($v as $key => $val) {
                    $title[] = $key;
                }
                break;
            }
        }

        $type = strtolower($type);

        switch ($type) {
            case 'csv':
                $content = self::export_csv($title, $list);
            break;
            default :
                $content = self::export_xls($title, $list);
            break;
        } 
        
        if (!$content) return false;
        
        if ($save) {
            \Swoole\Coroutine\System::writeFile($save, $content);
        }
        
        if ($dow) {
            if (is_string($dow)) {
                $name = $dow;
            } else {
                $name = date('YmdHis', time()).'_'.random_int(100, 999).'.'.$type;
            }
            switch ($type) {
                case 'csv':
                    $header = [
                        "Content-type" => "text/csv",
                        "Cache-Control" => "must-revalidate,post-check=0,pre-check=0",
                        'Expires' => '0',
                        'Pragma' => 'public',
                        'Content-disposition'=>'attachment; filename='.$name,
                    ];
                break;
                default :
                    $header = [
                        "Content-type" => "application/vnd.ms-excel",
                        "Cache-Control" => "must-revalidate,post-check=0,pre-check=0",
                        'Expires' => '0',
                        'Pragma' => 'public',
                        'Content-disposition'=>'attachment; filename='.$name,
                    ];
                break;
            }
            $Http = new \x\controller\Http();
            return $Http->fetch($content, 200, $header);
        }

        return true;
    }

    // 导出csv
    private static function export_csv($title, $list) {
        $fp = fopen('php://output', 'w+');
        if (!$fp) return false;

        $data = chr(0xEF) . chr(0xBB) . chr(0xBF);// 转码 防止乱码(比如微信昵称)
        foreach ($title as $v) {
            $data .= $v.',';
        }
        $data .= "\n";
        
        foreach ($list as $val) {
            foreach ($val as $v) {
                $data .= $v.",";
            }
            $data .= "\n";
        }
        
        return $data;
    }

    // 导出xls
    private static function export_xls($title, $list) {
        $data = '';
        foreach ($title as $v) {
            $data .= $v."\t";
        }
        $data .= "\n";

        foreach ($list as $val) {
            foreach ($val as $v) {
                $data .= $v."\t";
            }
            $data .= "\n";
        }

        return $data;
    }
}