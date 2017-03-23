<?php

namespace Hhmedia\Tags\Block;

/**
 * Tags content block
 */
class Tags extends \Magento\Framework\View\Element\Template
{
    /**
     * Tags collection
     *
     * @var Hhmedia\Tags\Model\ResourceModel\Tags\Collection
     */
    protected $_tagsCollection = null;
    
    /**
     * Tags factory
     *
     * @var \Hhmedia\Tags\Model\TagsFactory
     */
    protected $_tagsCollectionFactory;
    
    /** @var \Hhmedia\Tags\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hhmedia\Tags\Model\ResourceModel\Tags\CollectionFactory $tagsCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hhmedia\Tags\Model\ResourceModel\Tags\CollectionFactory $tagsCollectionFactory,
        \Hhmedia\Tags\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_tagsCollectionFactory = $tagsCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve tags collection
     *
     * @return Hhmedia\Tags\Model\ResourceModel\Tags\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_tagsCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared tags collection
     *
     * @return Hhmedia\Tags\Model\ResourceModel\Tags\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_tagsCollection)) {
            $this->_tagsCollection = $this->_getCollection();
            $this->_tagsCollection->setCurPage($this->getCurrentPage());
            $this->_tagsCollection->setPageSize($this->_dataHelper->getTagsPerPage());
            $this->_tagsCollection->setOrder('published_at','asc');
        }

        return $this->_tagsCollection;
    }
    
    /**
     * Fetch the current page for the tags list
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return $this->getData('current_page') ? $this->getData('current_page') : 1;
    }
    
    /**
     * Return URL to item's view page
     *
     * @param Hhmedia\Tags\Model\Tags $tagsItem
     * @return string
     */
    public function getItemUrl($tagsItem)
    {
        return $this->getUrl('*/*/view', array('id' => $tagsItem->getId()));
    }
    
    /**
     * Return URL for resized Tags Item image
     *
     * @param Hhmedia\Tags\Model\Tags $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width)
    {
        return $this->_dataHelper->resize($item, $width);
    }
    
    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('tags_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $tagsPerPage = $this->_dataHelper->getTagsPerPage();

            $pager->setAvailableLimit([$tagsPerPage => $tagsPerPage]);
            $pager->setTotalNum($this->getCollection()->getSize());
            $pager->setCollection($this->getCollection());
            $pager->setShowPerPage(TRUE);
            $pager->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );

            return $pager->toHtml();
        }

        return NULL;
    }
}
