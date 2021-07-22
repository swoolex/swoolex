<?php
/**
 * +----------------------------------------------------------------------
 * 分页样式接口类
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x;

abstract class Page {

    /**
     * 数据集
     */
    protected $items = [];
    /**
     * 当前页
     */
    protected $currentPage;
    /**
     * 最后一页
     */
    protected $lastPage;
    /**
     * 数据总数
     */
    protected $total;
    /**
     * 每页数量
     */
    protected $listRows;
    /**
     * 是否有下一页
     */
    protected $hasMore;
    /**
     * 分页配置
     */
    protected $options = [
        'var_page' => 'page',
        'query'    => [],
        'fragment' => '',
    ];

    /**
     * 构造函数
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param array $items 数据集
     * @param int $listRows 每页数
     * @param int $currentPage 当前页
     * @param int $total 数据总长度
     * @param array $options 分页配置
     * @return void
    */
    public function __construct($items, $listRows, $currentPage = null, $total = null, $options = []) {
        $this->options = array_merge($this->options, $options);

        $this->total       = $total;
        $this->lastPage    = (int) ceil($total / $listRows);
        $this->currentPage = $this->setCurrentPage($currentPage);
        $this->hasMore     = $this->currentPage < $this->lastPage;

        $this->items       = $items;
    }

    /**
     * 计算出当前页
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param int $currentPage 当前页
     * @return void
    */
    protected function setCurrentPage($currentPage) {
        if ($currentPage > $this->lastPage) {
            return $this->lastPage > 0 ? $this->lastPage : 1;
        }

        return $currentPage;
    }

    /**
     * 生成URL
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param int $page 分页数
     * @return string
    */
    protected function url($page) {
        if ($page <= 0) {
            $page = 1;
        }

        $parameters = [$this->options['var_page'] => $page];
        
        if (count($this->options['query']) > 0) {
            $parameters = array_merge($this->options['query'], $parameters);
        }
        
        $url = \x\Request::baseUrl();
        if (!empty($parameters)) {
            $url .= '?' . http_build_query($parameters, null, '&');
        }

        return $url . $this->buildFragment();
    }

    /**
     * 构造锚点字符串
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return string|null
    */
    protected function buildFragment() {
        return $this->options['fragment'] ? '#' . $this->options['fragment'] : '';
    }

    /**
     * 创建一组分页链接
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @param int $start 开始
     * @param int $end 结束
     * @return void
    */
    public function getUrlRange($start, $end) {
        $urls = [];

        for ($page = $start; $page <= $end; $page++) {
            $urls[$page] = $this->url($page);
        }

        return $urls;
    }

    /**
     * 数据是否足够分页
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function hasPages() {
        return !(1 == $this->currentPage && !$this->hasMore);
    }

    /**
     * 获取总记录数
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function total() {
        return $this->total;
    }
    /**
     * 获取每页长度
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function listRows() {
        return $this->listRows;
    }
    /**
     * 获取当前页数
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function currentPage() {
        return $this->currentPage;
    }
    /**
     * 获取最后一页数
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function lastPage() {
        return $this->lastPage;
    }

    /**
     * 获取分页结果集
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    public function list() {
        return $this->items;
    }

    /*--------------------------------------------- 必须要实现的接口 --------------------------------------------*/

    /**
     * 渲染分页html
     * @todo 无
     * @author 小黄牛
     * @version v2.0.1 + 2021.2.5
     * @deprecated 暂不启用
     * @global 无
     * @return void
    */
    abstract public function render();
}