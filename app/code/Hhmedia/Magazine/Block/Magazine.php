<?php

namespace Hhmedia\Magazine\Block;

use Hhmedia\Magazine\Model\MagazineFactory;

/**
 * Magazine content block
 */
class Magazine extends \Magento\Framework\View\Element\Template
{
    /**
     * Magazine collection
     *
     * @var Hhmedia\Magazine\Model\ResourceModel\Magazine\Collection
     */
    protected $_magazineCollection = null;

    protected $_productRepository;

    protected $magazineFactory;
    
    /**
     * Magazine factory
     *
     * @var \Hhmedia\Magazine\Model\MagazineFactory
     */
    protected $_magazineCollectionFactory;
    
    /** @var \Hhmedia\Magazine\Helper\Data */
    protected $_dataHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Hhmedia\Magazine\Model\ResourceModel\Magazine\CollectionFactory $magazineCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Hhmedia\Magazine\Model\ResourceModel\Magazine\CollectionFactory $magazineCollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        MagazineFactory $magazineFactory,
        \Hhmedia\Magazine\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_magazineCollectionFactory = $magazineCollectionFactory;
        $this->_productRepository = $productRepository;
        $this->magazineFactory = $magazineFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve magazine collection
     *
     * @return Hhmedia\Magazine\Model\ResourceModel\Magazine\Collection
     */
    protected function _getCollection()
    {
        $collection = $this->_magazineCollectionFactory->create();
        return $collection;
    }
    
    /**
     * Retrieve prepared magazine collection
     *
     * @return Hhmedia\Magazine\Model\ResourceModel\Magazine\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_magazineCollection)) {
            $this->_magazineCollection = $this->_getCollection();
            $this->_magazineCollection->setCurPage($this->getCurrentPage());
            $this->_magazineCollection->setPageSize($this->_dataHelper->getMagazinePerPage());
            $this->_magazineCollection->setOrder('published_at','asc');
        }

        return $this->_magazineCollection;
    }
    
    /**
     * Fetch the current page for the magazine list
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
     * @param Hhmedia\Magazine\Model\Magazine $magazineItem
     * @return string
     */
    public function getItemUrl($magazineItem)
    {
        return $this->getUrl('*/*/view', array('id' => $magazineItem->getId()));
    }
    
    /**
     * Return URL for resized Magazine Item image
     *
     * @param Hhmedia\Magazine\Model\Magazine $item
     * @param integer $width
     * @return string|false
     */
    public function getImageUrl($item, $width)
    {
        return $this->_dataHelper->resize($item, $width);
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getMagazine($magazineId)
    {
        $magazine   = $this->magazineFactory->create();
        if ($magazineId) {
            $magazine->load($magazineId);
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Hhmedia\Magazine\Model\Magazine');
        $products  =  $model->getProducts($magazine);
        return $products;
    }
    
    /**
     * Get a pager
     *
     * @return string|null
     */
    public function getPager()
    {
        $pager = $this->getChildBlock('magazine_list_pager');
        if ($pager instanceof \Magento\Framework\Object) {
            $magazinePerPage = $this->_dataHelper->getMagazinePerPage();

            $pager->setAvailableLimit([$magazinePerPage => $magazinePerPage]);
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
