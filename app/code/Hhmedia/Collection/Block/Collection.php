<?php

namespace Hhmedia\Collection\Block;

/**
 * Collection content block
 */
class Collection extends \Magento\Framework\View\Element\Template
{
    /**
     * Collection collection
     *
     * @var Hhmedia\Collection\Model\ResourceModel\Collection\Collection
     */
    protected $_collectionCollection = null;
    
    /**
     * Collection factory
     *
     * @var \Hhmedia\Collection\Model\CollectionFactory
     */
    protected $_collectionCollectionFactory;
    
    /** @var \Hhmedia\Collection\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hhmedia\Collection\Model\ResourceModel\Collection\CollectionFactory $collectionCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hhmedia\Collection\Model\ResourceModel\Collection\CollectionFactory $collectionCollectionFactory,
        \Hhmedia\Collection\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_collectionCollectionFactory = $collectionCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve collection collection
     *
     * @return Hhmedia\Collection\Model\ResourceModel\Collection\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_collectionCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared collection collection
     *
     * @return Hhmedia\Collection\Model\ResourceModel\Collection\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_collectionCollection)) {
            $this->_collectionCollection = $this->_getCollection();
            $this->_collectionCollection->setCurPage($this->getCurrentPage());
            $this->_collectionCollection->setPageSize($this->_dataHelper->getCollectionPerPage());
            $this->_collectionCollection->setOrder('published_at','asc');
        }

        return $this->_collectionCollection;
    }
    
    /**
     * Fetch the current page for the collection list
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
     * @param Hhmedia\Collection\Model\Collection $collectionItem
     * @return string
     */
    public function getItemUrl($collectionItem)
    {
        return $this->getUrl('*/*/view', array('id' => $collectionItem->getId()));
    }
    
    /**
     * Return URL for resized Collection Item image
     *
     * @param Hhmedia\Collection\Model\Collection $item
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
        $pager = $this->getChildBlock('collection_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $collectionPerPage = $this->_dataHelper->getCollectionPerPage();

            $pager->setAvailableLimit([$collectionPerPage => $collectionPerPage]);
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
