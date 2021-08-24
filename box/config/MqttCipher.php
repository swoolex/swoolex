<?php
/**
 * +----------------------------------------------------------------------
 * MQTT密码器
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace box\config;
use design\AbstractMqttCipher;

class MqttCipher extends AbstractMqttCipher {
    /**
     * 必须实现的密码器入口方法
     * @todo 无
     * @author 小黄牛
     * @version v2.5.1 + 2021.08.20
     * @deprecated 暂不启用
     * @global 无
     * @param array $data 连接请求参数
     * @return bool true.鉴权通过  false.鉴权失败
    */
    public function run($data) {
        /**
         * $data["client_id"] 设备ID
         * $data["user_name"] 连接账号
         * $data["password"]连接密码
        */

        // 下方IF为系统密码器逻辑，你可以改成读取DB或Redis存储器做校验
        if ($data['user_name'] != \x\Config::get('mqtt.user_name') || $data['password'] != \x\Config::get('mqtt.password')) {
            return false;
        }

        return true;
    }
}