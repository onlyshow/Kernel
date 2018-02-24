<?php

namespace Kernel\Utilities;

class Paginator
{
    const NUM_PLACEHOLDER = 'num';
    protected $totalItems;
    protected $numPages;
    protected $itemsPerPage;
    protected $currentPage;
    protected $urlPattern;
    protected $maxPagesToShow = 9;
    // protected $previousText = '上一页';
    // protected $nextText = '下一页';
    /**
     * @param int $totalItems
     * @param int $itemsPerPage
     * @param int $currentPage
     * @param string $urlPattern
     */
    public function __construct($totalItems, $itemsPerPage, $currentPage, $urlPattern = '')
    {
        $this->totalItems = $totalItems;
        $this->itemsPerPage = $itemsPerPage;
        $this->currentPage = $currentPage;
        $this->urlPattern = $urlPattern;
        $this->updateNumPages();
    }
    protected function updateNumPages()
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int) ceil($this->totalItems/$this->itemsPerPage));
    }
    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     */
    public function setMaxPagesToShow($maxPagesToShow)
    {
        if ($maxPagesToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }
        $this->maxPagesToShow = $maxPagesToShow;
    }
    /**
     * @return int
     */
    public function getMaxPagesToShow()
    {
        return $this->maxPagesToShow;
    }
    /**
     * @param int $currentPage
     */
    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }
    /**
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }
    /**
     * @param int $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPages();
    }
    /**
     * @return int
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }
    /**
     * @param int $totalItems
     */
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
        $this->updateNumPages();
    }
    /**
     * @return int
     */
    public function getTotalItems()
    {
        return $this->totalItems;
    }
    /**
     * @return int
     */
    public function getNumPages()
    {
        return $this->numPages;
    }
    /**
     * @param string $urlPattern
     */
    public function setUrlPattern($urlPattern)
    {
        $this->urlPattern = $urlPattern;
    }
    /**
     * @return string
     */
    public function getUrlPattern()
    {
        return $this->urlPattern;
    }
    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl($pageNum)
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }
    public function getNextPage()
    {
        if ($this->currentPage < $this->numPages) {
            return $this->currentPage + 1;
        }
        return null;
    }
    public function getPrevPage()
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }
        return null;
    }
    public function getNextUrl()
    {
        if (!$this->getNextPage()) {
            return null;
        }
        return $this->getPageUrl($this->getNextPage());
    }
    /**
     * @return string|null
     */
    public function getPrevUrl()
    {
        if (!$this->getPrevPage()) {
            return null;
        }
        return $this->getPageUrl($this->getPrevPage());
    }
    /**
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages()
    {
        $pages = array();
        if ($this->numPages <= 1) {
            return array();
        }
        if ($this->numPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->numPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {
            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);
            if ($this->currentPage + $numAdjacents > $this->numPages) {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if ($slidingStart < 2) {
                $slidingStart = 2;
            }
            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if ($slidingEnd >= $this->numPages) {
                $slidingEnd = $this->numPages - 1;
            }
            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->currentPage == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if ($slidingEnd < $this->numPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }
        return $pages;
    }
    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return Array
     */
    protected function createPage($pageNum, $isCurrent = false)
    {
        return array(
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );
    }
    /**
     * @return array
     */
    protected function createPageEllipsis()
    {
        return array(
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        );
    }
    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml()
    {
        if ($this->numPages <= 1) {
            return '';
        }
        $html = '<div class="layui-box layui-laypage layui-laypage-default">';
        if ($this->getPrevUrl()) {
            // $html .= '<a class="layui-laypage-prev" href="' . $this->getPrevUrl() . '">'. $this->previousText .'</a>';
        }
        foreach ($this->getPages() as $page) {
            if ($page['url']) {
                if ($page['isCurrent']) {
                    $html .= '<span class="layui-laypage-curr"><em class="layui-laypage-em"></em><em>'.$page['num'].'</em></span>';
                } else {
                    $html .= '<a href="'.$page['url'].'">'.$page['num'].'</a>';
                }
            } else {
                $html .= '<span>' . $page['num'] . '</span>';
            }
        }
        if ($this->getNextUrl()) {
            // $html .= '<a  class ="layui-laypage-next" href="' . $this->getNextUrl() . '">'. $this->nextText .'</a>';
        }
        $html .= '</div>';
        return $html;
    }
    public function __toString()
    {
        return $this->toHtml();
    }
    public function getCurrentPageFirstItem()
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;
        if ($first > $this->totalItems) {
            return null;
        }
        return $first;
    }
    public function getCurrentPageLastItem()
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }
        $last = $first + $this->itemsPerPage - 1;
        if ($last > $this->totalItems) {
            return $this->totalItems;
        }
        return $last;
    }
    public function setPreviousText($text)
    {
        $this->previousText = $text;
        return $this;
    }
    public function setNextText($text)
    {
        $this->nextText = $text;
        return $this;
    }
}
