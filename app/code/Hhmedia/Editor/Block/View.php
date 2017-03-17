<?php

namespace Hhmedia\Editor\Block;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Hhmedia\Editor\Helper\Data */
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
        \Hhmedia\Editor\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->_productRepository = $productRepository;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getEditor()->getTitle());
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Hhmedia\Editor\Model\Editor
     */
    public function getEditor()
    {
        return $this->_coreRegistry->registry('current_editor');
    }

    public function getProductData($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getProductCollection()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Hhmedia\Editor\Model\Editor');
        $products  =  $model->getProducts($this->getEditor());
        return $products;
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('editor');
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

    public function getProductPrice($price){
        $objPrice = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
        $priceHelper = $objPrice->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice ;
    }

}