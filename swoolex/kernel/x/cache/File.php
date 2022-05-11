<?php
/**
 * +----------------------------------------------------------------------
 * File文件存储 - 缓存驱动
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\cache;
use design\AbstractCacheDriver;

class File extends AbstractCacheDriver
{
    /**
     * 缓存文件目录
    */
    private $path;
    /**
     * 后缀名称
    */
    private $suffix = '.cache';

    /**
     * 初始化配置
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param array $config
    */
    public function __construct($config) {
        $this->config = $config;
        $this->path = WORKLOG_PATH.DS.'cache'.DS;
        if (!is_dir($this->path)) {
            $res = mkdir($this->path, 0755, true);
            if (!$res) {
                throw new \Exception("Cache File Directory ".$key." creation failed");
            }
        }
    }

    /**
     * 设置缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $val 值
     * @param int $prefix 有效期(S)
     * @return bool
    */
    public function set($key, $val, $prefix=null) {
        $key = $this->config['prefix'].$key;
        if (is_null($prefix)) $prefix = $this->config['expire'];
        if ($prefix) {
            $outtime = time()+$prefix;
        } else {
            $outtime = 0;
        }
        $cache = $outtime.'|'.$this->_encode($val);
        return \Swoole\Coroutine\System::writeFile($this->path.$key.$this->suffix, $cache);
    }

    /**
     * 缓存是否有效存在
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param bool $status 是否返回内容
     * @return false|string
    */
    public function has($key, $status=false) {
        $path = $this->path.$this->config['prefix'].$key.$this->suffix;
        if (!file_exists($path)) return false;
        $cache = \Swoole\Coroutine\System::readFile($path);
        $end = strpos($cache, '|');
        $time = substr($cache, 0, $end);
        
        if ($time != 0 && $time < time()) return false;
        if ($status === true) return $this->_decode(substr($cache, $end+1));
        return true;
    }

    /**
     * 获取缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param mixed $default 不存在时默认返回值
     * @return mixed
    */
    public function get($key, $default=null) {
        $ret = $this->has($key, true);

        if ($ret !== false) return $ret;
        if ($default !== null ) return $default;

        return false;
    }

    /**
     * 数字类型自增 - 不是原子级
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自增值
     * @return mixed
    */
    public function inc($key, $num=1) {
        $int = $this->has($key, true);
        if ($int === false) return false;
        if (!is_numeric($int)) return false;

        $val = $int+$num;
        
        $path = $this->path.$this->config['prefix'].$key.$this->suffix;
        if (!file_exists($path)) return false;
        $cache = \Swoole\Coroutine\System::readFile($path);
        $end = strpos($cache, '|');
        $outtime = substr($cache, 0, $end);
        $cache = $outtime.'|'.$this->_encode($val);
        return \Swoole\Coroutine\System::writeFile($path, $cache);
    }

    /**
     * 数字类型自减 - 不是原子级
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @param int $num 自减值
     * @return mixed
    */
    public function dec($key, $num=1) {
        $int = $this->has($key, true);
        if ($int === false) return false;
        if (!is_numeric($int)) return false;

        $val = $int-$num;
        if ($val < 0) $val = 0;
        
        $path = $this->path.$this->config['prefix'].$key.$this->suffix;
        if (!file_exists($path)) return false;
        $cache = \Swoole\Coroutine\System::readFile($path);
        $end = strpos($cache, '|');
        $outtime = substr($cache, 0, $end);
        $cache = $outtime.'|'.$this->_encode($val);
        return \Swoole\Coroutine\System::writeFile($path, $cache);
    }

    /**
     * 删除缓存
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return bool
    */
    public function rm($key) {
        $path = $this->path.$this->config['prefix'].$key.$this->suffix;
        if (!file_exists($path)) return false;

        return unlink($path);
    }

    /**
     * 删除缓存并获取
     * @author 小黄牛
     * @version v2.5.8 + 2021-11-02
     * @param string $key 键
     * @return mixed
    */
    public function pull($key) {
        $cache = $this->get($key);
        $this->rm($key);
        return $cache;
    }
}
