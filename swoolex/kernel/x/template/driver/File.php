<?php
/**
 * +----------------------------------------------------------------------
 * 模板引擎缓存类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：出自 https://github.com/top-think/think-view
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\template\driver;

class File {
    /**
     * 缓存文件路径
    */
    protected $cacheFile;

    /**
     * 写入编译缓存
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $cacheFile 缓存的文件名
     * @param string $content 缓存的内容
    */
    public function write($cacheFile, $content) {
        // 检测模板目录
        $dir = dirname($cacheFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // 生成模板缓存文件
        if (false === \Swoole\Coroutine\System::writeFile($cacheFile, $content)) {
            throw new \Exception('cache write error:' . $cacheFile);
        }
    }

    /**
     * 读取编译编译
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $cacheFile 缓存的文件名
     * @param array $vars 变量数组
    */
    public function read($cacheFile, $vars = []) {
        $this->cacheFile = $cacheFile;

        if (!empty($vars) && is_array($vars)) {
            // 模板阵列变量分解成为独立变量
            extract($vars, EXTR_OVERWRITE);
        }

        //载入模版缓存文件
        include $this->cacheFile;
    }

    /**
     * 检查编译缓存是否有效
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $cacheFile 缓存的文件名
     * @param int $cacheTime 缓存时间
     * @return bool
    */
    public function check($cacheFile, $cacheTime) {
        // 缓存文件不存在, 直接返回false
        if (!file_exists($cacheFile)) {
            return false;
        }

        if (0 != $cacheTime && time() > filemtime($cacheFile) + $cacheTime) {
            // 缓存是否在有效期
            return false;
        }

        return true;
    }
}
