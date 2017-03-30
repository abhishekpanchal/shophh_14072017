<?php

namespace Hhmedia\Magazine\Block;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Hhmedia\Magazine\Helper\Data */
    protected $_dataHelper;

    protected $_productRepository;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Hhmedia\Magazine\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->_dataHelper = $dataHelper;
        $this->_productRepository = $productRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getMagazine()->getTitle());
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Hhmedia\Magazine\Model\Magazine
     */
    public function getMagazine()
    {
        return $this->_coreRegistry->registry('current_magazine');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('magazine/index/index');
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return string
     */
    public function getBackTitle()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return __('Back to My Orders');
        }
        return __('View Another Order');
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

    public function getProductData($id)
    {
        return $this->_productRepository->getById($id);
    }
    
    public function getProductCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Hhmedia\Magazine\Model\Magazine');
        $products  =  $model->getProducts($this->getMagazine());
        return $products;
    }

    public function getProductPrice($price){
        $objPrice = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objPrice->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice ;
    }

}
