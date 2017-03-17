<?php

namespace Hhmedia\Editor\Block;

/**
 * Editor content block
 */
class Editor extends \Magento\Framework\View\Element\Template
{
    /**
     * Editor collection
     *
     * @var Hhmedia\Editor\Model\ResourceModel\Editor\Collection
     */
    protected $_editorCollection = null;
    
    /**
     * Editor factory
     *
     * @var \Hhmedia\Editor\Model\EditorFactory
     */
    protected $_editorCollectionFactory;
    
    /** @var \Hhmedia\Editor\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hhmedia\Editor\Model\ResourceModel\Editor\CollectionFactory $editorCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hhmedia\Editor\Model\ResourceModel\Editor\CollectionFactory $editorCollectionFactory,
        \Hhmedia\Editor\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_editorCollectionFactory = $editorCollectionFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve editor collection
     *
     * @return Hhmedia\Editor\Model\ResourceModel\Editor\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_editorCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared editor collection
     *
     * @return Hhmedia\Editor\Model\ResourceModel\Editor\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_editorCollection)) {
            $this->_editorCollection = $this->_getCollection();
            $this->_editorCollection->setCurPage($this->getCurrentPage());
            $this->_editorCollection->setPageSize($this->_dataHelper->getEditorPerPage());
            $this->_editorCollection->setOrder('published_at','asc');
        }

        return $this->_editorCollection;
    }

    public function getGuestEditor(){
        $guestEditor = $this->_getCollection()
                    ->addFieldToFilter('past', 0)
                    ->addFieldToFilter('guest',1);
        return $guestEditor;
    }

    
    /**
     * Fetch the current page for the editor list
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
     * @param Hhmedia\Editor\Model\Editor $editorItem
     * @return string
     */
    public function getItemUrl($editorItem)
    {
        return $this->getUrl('*/*/view', array('id' => $editorItem->getId()));
    }
    
    /**
     * Return URL for resized Editor Item image
     *
     * @param Hhmedia\Editor\Model\Editor $item
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
        $pager = $this->getChildBlock('editor_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $editorPerPage = $this->_dataHelper->getEditorPerPage();

            $pager->setAvailableLimit([$editorPerPage => $editorPerPage]);
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
