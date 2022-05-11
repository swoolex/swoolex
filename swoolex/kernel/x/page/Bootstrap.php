<?php
/**
 * +----------------------------------------------------------------------
 * 分页Bootstrap样式
 * +----------------------------------------------------------------------
 * 官网：https://www.sw-x.cn
 * +----------------------------------------------------------------------
 * 作者：小黄牛 <1731223728@qq.com>
 * +----------------------------------------------------------------------
 * 开源协议：http://www.apache.org/licenses/LICENSE-2.0
 * +----------------------------------------------------------------------
*/

namespace x\page;
use x\Page;

class Bootstrap extends Page {

    /**
     * 上一页按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $text
     * @return string
    */
    protected function getPreviousButton($text = "&laquo;") {

        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text);
    }

     /**
      * 下一页按钮
      * @todo 无
      * @author 小黄牛
      * @version v2.0.10 + 2021.07.01
      * @deprecated 暂不启用
      * @global 无
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '&raquo;') {
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 页码按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @return string
    */
    protected function getLinks() {

        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null,
        ];

        $side   = 3;
        $window = $side * 2;

        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 2), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 渲染分页html
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @return mixed
    */
    public function render() {
        if ($this->hasPages()) {
            return sprintf(
                '<ul class="pagination">%s %s %s</ul>',
                $this->getPreviousButton(),
                $this->getLinks(),
                $this->getNextButton()
            );
        }
    }

    /**
     * 生成一个可点击的按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $url
     * @param int $page
     * @return string
    */
    protected function getAvailablePageWrapper($url, $page) {
        return '<li><a href="' . htmlentities($url) . '">' . $page . '</a></li>';
    }

    /**
     * 生成一个禁用的按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $text
     * @return string
    */
    protected function getDisabledTextWrapper($text) {
        return '<li class="disabled"><span>' . $text . '</span></li>';
    }

    /**
     * 生成一个激活的按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $text
     * @return string
    */
    protected function getActivePageWrapper($text) {
        return '<li class="active"><span>' . $text . '</span></li>';
    }

    /**
     * 生成省略号按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @return string
    */
    protected function getDots() {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param array $urls
     * @return string
    */
    protected function getUrlLinks(array $urls) {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     * @author 小黄牛
     * @version v2.0.10 + 2021.07.01
     * @param string $url
     * @param int $page
     * @return string
    */
    protected function getPageLinkWrapper($url, $page) {
        if ($this->currentPage() == $page) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }
}
