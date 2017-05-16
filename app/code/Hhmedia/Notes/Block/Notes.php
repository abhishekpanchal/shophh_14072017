<?php

namespace Hhmedia\Notes\Block;

/**
 * Notes content block
 */
class Notes extends \Magento\Framework\View\Element\Template
{
    /**
     * Notes collection
     *
     * @var Hhmedia\Notes\Model\ResourceModel\Notes\Collection
     */
    protected $_notesCollection = null;
    
    /**
     * Notes factory
     *
     * @var \Hhmedia\Notes\Model\NotesFactory
     */
    protected $_notesCollectionFactory;
    
    /** @var \Hhmedia\Notes\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hhmedia\Notes\Model\ResourceModel\Notes\CollectionFactory $notesCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hhmedia\Notes\Model\ResourceModel\Notes\CollectionFactory $notesCollectionFactory,
        \Hhmedia\Notes\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_notesCollectionFactory = $notesCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve notes collection
     *
     * @return Hhmedia\Notes\Model\ResourceModel\Notes\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_notesCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared notes collection
     *
     * @return Hhmedia\Notes\Model\ResourceModel\Notes\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_notesCollection)) {
            $this->_notesCollection = $this->_getCollection();
            $this->_notesCollection->setCurPage($this->getCurrentPage());
            $this->_notesCollection->setPageSize($this->_dataHelper->getNotesPerPage());
            $this->_notesCollection->setOrder('published_at','asc');
        }

        return $this->_notesCollection;
    }
    
    /**
     * Fetch the current page for the notes list
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
     * @param Hhmedia\Notes\Model\Notes $notesItem
     * @return string
     */
    public function getItemUrl($notesItem)
    {
        return $this->getUrl('*/*/view', array('id' => $notesItem->getId()));
    }
    
    /**
     * Return URL for resized Notes Item image
     *
     * @param Hhmedia\Notes\Model\Notes $item
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
        $pager = $this->getChildBlock('notes_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $notesPerPage = $this->_dataHelper->getNotesPerPage();

            $pager->setAvailableLimit([$notesPerPage => $notesPerPage]);
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
