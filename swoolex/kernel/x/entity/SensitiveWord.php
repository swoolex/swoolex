<?php
/**
 * +----------------------------------------------------------------------
 * 敏感词检测
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\entity;
use design\AbstractSingleCase;
use x\common\HashMap;

class SensitiveWord
{
    use AbstractSingleCase;

    /**
     * 词库个数
    */
    protected $wordLength = 0;
    /**
     * 待检测语句长度
    */
    protected $contentLength = 0;
    /**
     * 敏感词库树
    */
    protected $wordTree = null;

    /**
     * 构建敏感词树【文件模式】
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $filepath 文件地址
     * @param string $char 分隔符，false表示按行分隔
     * @return void
    */
    public function set_tree_file($filepath = '', $char=false) {
        $this->init();

        if (!file_exists($filepath)) {
            throw new \Exception('词库文件不存在');
            return false;
        }

        // 按行读取文件
        if ($char === false) {
            $fp = fopen($filepath, 'r');
            while (! feof($fp)) {
                $this->build_tree(trim(fgets($fp)));
            }
            fclose($fp);
        } else {
            $sensitiveWords = explode($char, file_get_contents($filepath));
            return $this->set_tree_array($sensitiveWords);
        }

        return $this;
    }

    /**
     * 构建敏感词树【数组模式】
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param array $sensitiveWords 一维数组
     * @param string $field 字段名[当传递该参数时，为二维数组]
     * @return void
    */
    public function set_tree_array($sensitiveWords = null, $field=false) {
        $this->init();

        if (empty($sensitiveWords)) {
            throw new \Exception('词库不能为空');
            return false;
        }

        if ($field === false) {
            foreach ($sensitiveWords as $word) {
                $this->build_tree($word);
            }
        } else {
            foreach ($sensitiveWords as $word) {
                $this->build_tree($word[$field]);
            }
        }
        
        return $this;
    }

    /**
     * 构建敏感词树【单条记录】
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @param string $word 敏感词
     * @return void
    */
    public function set_tree_string($word) {
        $this->init();

        return $this->build_tree($word);
    }
    
    /**
     * 检测文字中的敏感词
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $content 待检测内容
     * @param int $wordNum 需要获取的敏感词数量 [默认获取全部]
     * @return void
    */
    public function exec($content, $wordNum = 0) {
        $this->contentLength = mb_strlen($content, 'utf-8');
        $badWordList = [];
        for ($length=0; $length < $this->contentLength; $length++) {
            $matchFlag = 0;
            $flag = false;
            $tempMap = $this->wordTree;
            for ($i = $length; $i < $this->contentLength; $i++) {
                $keyChar = mb_substr($content, $i, 1, 'utf-8');

                $nowMap = $tempMap->get($keyChar);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;

                $matchFlag++;

                if (false === $nowMap->get('ending')) {
                    continue;
                }

                $flag = true;
            }

            if (! $flag) {
                $matchFlag = 0;
            }

            if ($matchFlag <= 0) {
                continue;
            }

            $badWordList[] = mb_substr($content, $length, $matchFlag, 'utf-8');

            if ($wordNum > 0 && count($badWordList) == $wordNum) {
                return $badWordList;
            }

            $length = $length + $matchFlag - 1;
        }
        return $badWordList;
    }

    /**
     * 替换敏感字字符
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $content 文本内容
     * @param string $replaceChar 替换字符
     * @param bool $repeat true=>重复替换为敏感词相同长度的字符
    */
    public function replace($content, $replaceChar = '', $repeat = true) {
        if (empty($content)) {
            throw new \Exception('请填写检测的内容');
            return false;
        }

        $badWordList = $this->exec($content);

        if (empty($badWordList)) {
            return $content;
        }
        $badWordList = array_unique($badWordList);

        foreach ($badWordList as $badWord) {
            $hasReplacedChar = $replaceChar;
            if ($repeat) {
                $hasReplacedChar = $this->start_replace($badWord, $replaceChar);
            }
            $content = str_replace($badWord, $hasReplacedChar, $content);
        }
        return $content;
    }

    /**
     * 标记敏感词
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $content 文本内容
     * @param string $sTag 标签开头，如<mark>
     * @param string $eTag 标签结束，如</mark>
    */
    public function mark($content, $sTag, $eTag) {
        if (empty($content)) {
            throw new \Exception('请填写检测的内容');
            return false;
        }

        $badWordList = $this->exec($content);

        if (empty($badWordList)) {
            return $content;
        }
        $badWordList = array_unique($badWordList);
        
        foreach ($badWordList as $badWord) {
            $replaceChar = $sTag . $badWord . $eTag;
            $content = str_replace($badWord, $replaceChar, $content);
        }
        return $content;
    }

    /**
     * 被检测内容是否合法
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $content 文本内容
     * @return bool
    */
    public function is_legal($content) {
        $this->contentLength = mb_strlen($content, 'utf-8');

        for ($length = 0; $length < $this->contentLength; $length++) {
            $matchFlag = 0;

            $tempMap = $this->wordTree;
            for ($i = $length; $i < $this->contentLength; $i++) {
                $keyChar = mb_substr($content, $i, 1, 'utf-8');

                $nowMap = $tempMap->get($keyChar);

                if (empty($nowMap)) {
                    break;
                }

                $tempMap = $nowMap;
                $matchFlag++;

                if (false === $nowMap->get('ending')) {
                    continue;
                }

                return true;
            }

            if ($matchFlag <= 0) {
                continue;
            }

            $length = $length + $matchFlag - 1;
        }
        return false;
    }

    /**
     * 获取当前敏感词库长度
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function count() {
        return $this->wordLength;
    }

    /**
     * 清空词库
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function reset() {
        $this->wordTree = new HashMap();
    }

    /**
     * 将单个敏感词构建成树结构
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $word 敏感词
     * @return void
    */
    protected function build_tree($word = '') {
        if ('' === $word) {
            return false;
        }
        $tree = $this->wordTree;
        
        $wordLength = mb_strlen($word, 'utf-8');
        for ($i = 0; $i < $wordLength; $i++) {
            $keyChar = mb_substr($word, $i, 1, 'utf-8');

            $tempTree = $tree->get($keyChar);

            if ($tempTree) {
                $tree = $tempTree;
            } else {
                $newTree = new HashMap();
                $newTree->put('ending', false);

                $tree->put($keyChar, $newTree);
                $tree = $newTree;
            }

            if ($i == $wordLength - 1) {
                $tree->put('ending', true);
            }
        }
        $this->wordLength++;

        return true;
    }

    /**
     * 敏感词替换为对应长度的字符
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-23
     * @deprecated 暂不启用
     * @global 无
     * @param string $word 目标内容
     * @param mixed $char 替换内容
     * @return string
    */
    protected function start_replace($word, $char) {
        $str = '';
        $length = mb_strlen($word, 'utf-8');
        for ($counter = 0; $counter < $length; ++$counter) {
            $str .= $char;
        }

        return $str;
    }

    /**
     * 初始化词库树实例
     * @todo 无
     * @author 小黄牛
     * @version v2.5.12 + 2021-11-24
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    private function init() {
        if (!$this->wordTree) $this->wordTree = new HashMap();
    }
}