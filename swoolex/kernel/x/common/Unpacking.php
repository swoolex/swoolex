<?php
/**
 * +----------------------------------------------------------------------
 * 服务开箱初始化
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\common;

class Unpacking {

    /**
     * 开箱入口
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 服务类型
     * @return void
    */
    public static function run($type) {
        $res = self::create_app();

        self::switch_king($type, $res);

        \design\StartRecord::unpacking();
    }
    /**
     * 判断服务分支选择开箱流程
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param string $type 服务类型
     * @param bool $status 开箱状态
     * @return void
    */
    private static function switch_king($type, $status) {
        switch ($type) {
            case 'http':
                self::unpack_http(true);
            break;
            case 'websocket':
                self::unpack_http(true);
                // WebSocket组件，不是每次必备开箱
                if (!$status) return false;
                self::unpack_websocket(true);
            break;
            case 'rpc':
                // RPC组件，不是每次必备开箱
                if (!$status) return false;
                self::unpack_rpc();
            break;
            case 'mqtt':
                self::unpack_mqtt();
            break;
            default:break;
        }
    }
    /**
     * 创建工作根目录
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public static function create_app() {
        if (!is_dir(APP_PATH)) {
            mkdir(APP_PATH, 0755);
            return true;
        }

        return false;
    }

    //----------------------------------- 以下为开箱动作 ---------------------------------
    /**
     * HTTP 服务开箱
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否需要创建开箱控制器文件
     * @return void
    */
    public static function unpack_http($status=true) {
        $controller_path = APP_PATH.'http';
        $view_path = APP_PATH.'view';

        if (!is_dir($controller_path)) mkdir($controller_path, 0755);
        if (!is_dir($view_path)) mkdir($view_path, 0755);

        if ($status) {
            $dir = $controller_path.DS.'Index.php';
            if (file_exists($dir)) return false;
    
            return copy(BUILT_PATH.'unpacking/http/Index.php', $dir);
        }
        
        return true;
    }
    /**
     * WebSocket 服务开箱
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @param bool $status 是否需要创建开箱控制器文件
     * @return void
    */
    public static function unpack_websocket($status=true) {
        $controller_path = APP_PATH.'websocket';

        if (!is_dir($controller_path)) mkdir($controller_path, 0755);
        
        if ($status) {
            $dir = $controller_path.DS.'Index.php';
            if (file_exists($dir)) return false;
            
            return copy(BUILT_PATH.'unpacking/websocket/Index.php', $dir);
        }
        
        return true;
    }
    /**
     * RPC 服务开箱
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function unpack_rpc() {
        $controller_path = APP_PATH.'rpc';

        if (!is_dir($controller_path)) mkdir($controller_path, 0755);

        $controller_path = $controller_path.DS.'order';
        if (!is_dir($controller_path)) mkdir($controller_path, 0755);

        $dir = $controller_path.DS.'create.php';
        if (file_exists($dir)) return false;
        
        return copy(BUILT_PATH.'unpacking/rpc/order/create.php', $dir);
    }
    /**
     * MQTT 服务开箱
     * @todo 无
     * @author 小黄牛
     * @version v2.5.0 + 2021.07.20
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private static function unpack_mqtt() {
        $controller_path = APP_PATH.'mqtt';

        if (!is_dir($controller_path)) mkdir($controller_path, 0755);

        $controller_path = $controller_path.DS.'system';
        if (!is_dir($controller_path)) mkdir($controller_path, 0755);

        $dir = $controller_path.DS.'index.php';
        if (file_exists($dir)) return false;
        
        return copy(BUILT_PATH.'unpacking/mqtt/system/index.php', $dir);
    }
}
