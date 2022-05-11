<?php
/**
 * +----------------------------------------------------------------------
 * 节点信息对象
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\elasticsearch\connections;
use x\elasticsearch\connections\ConnectionInterface;
use x\elasticsearch\tool\Client;
use Exception;

class Connection implements ConnectionInterface
{
    /**
     * 节点信息
    */
    private $node;
    /**
     * 请求头信息
    */
    private $headers;
    /**
     * 操作系统版本
     */
    private $OSVersion = null;

    /**
     * 初始化节点信息
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @param array $node
     * @return this
    */
    public function __construct($node) {
        $this->node = $node;

        $this->headers[] = 'Content-Type: application/json';
        $this->headers[] = 'User-Agent: '.sprintf(
            "elasticsearch-php/%s (%s %s; PHP %s)",
            Client::VERSION,
            PHP_OS,
            $this->getOSVersion(),
            phpversion()
        );
    }
    /**
     * 获取节点IP
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return array
    */
    public function getHost() {
        return $this->node['host'];
    } 

    /**
     * 获取节点配置
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return array
    */
    public function getNode() {
        return $this->node;
    } 

    /**
     * 获取请求头配置
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return array
    */
    public function getHeader() {
        return $this->headers;
    } 

    /**
     * 获取操作系统版本号
     * @author 小黄牛
     * @version v2.5.26 + 2022-05-06
     * @return array
    */
    private function getOSVersion() {
        if ($this->OSVersion === null) {
            $this->OSVersion = strpos(strtolower(ini_get('disable_functions')), 'php_uname') !== false
                ? ''
                : php_uname("r");
        }
        return $this->OSVersion;
    }
}
