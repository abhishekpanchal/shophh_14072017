<?php

namespace Hhmedia\Collection\Block;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /** @var \Hhmedia\Collection\Helper\Data */
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
        \Hhmedia\Collection\Helper\Data $dataHelper,
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
        $this->pageConfig->getTitle()->set($this->getCollection()->getTitle());
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Hhmedia\Collection\Model\Collection
     */
    public function getCollection()
    {
        return $this->_coreRegistry->registry('current_collection');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('collection/index/index');
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

    public function getProductBySku($sku)
    {
        try{
            $product = $this->_productRepository->get($sku);    
        }catch (\Exception $exception) {
            $product = null;
        }
        return $product;
    }

    public function getProductPrice($price){
        $objPrice = \Magento\Framework\App\ObjectManager::getInstance(); 
        $priceHelper = $objPrice->create('Magento\Framework\Pricing\Helper\Data'); 
        $formattedPrice = $priceHelper->currency($price, true, false);
        return $formattedPrice ;
    }

}