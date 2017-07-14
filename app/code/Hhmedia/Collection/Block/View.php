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

    /**
     * @var \Hhmedia\Collection\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Hhmedia\Collection\Helper\Data $dataHelper
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Hhmedia\Collection\Helper\Data $dataHelper,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        array $data = []
    ) {
        $this->_imageFactory = $imageFactory;
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
        $this->pageConfig->getTitle()->set($this->getCollection()->getCollectionTitle());
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

    public function resize($image, $width = null, $height = null)
    {
        $media = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $absolutePath = $media->getAbsolutePath('catalog/product').$image;
        $imageResized = $media->getAbsolutePath('catalog/product/collection/'.$width.'/').$image;

        $imageResize = $this->_imageFactory->create();
        $imageResize->open($absolutePath);
        $imageResize->constrainOnly(TRUE);
        $imageResize->keepTransparency(TRUE);
        $imageResize->keepFrame(TRUE);  
        $imageResize->keepAspectRatio(TRUE);
        $imageResize->backgroundColor(array(255, 255, 255));
        $imageResize->resize($width,$height);
        //destination folder       
        $destination = $imageResized;
        //save image
        $imageResize->save($destination);  

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/collection/'.$width.$image;
        return $resizedURL;
    }

}
