<?php

namespace Hhmedia\Tags\Block;

use Magento\Framework\App\Filesystem\DirectoryList;

class View extends \Magento\Framework\View\Element\Template
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Hhmedia\Tags\Helper\Data
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

    protected $_storeConfig;

    protected $_filesystem;
    
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
        \Hhmedia\Tags\Helper\Data $dataHelper,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        array $data = []
    ) {
        $this->_imageFactory = $imageFactory;
        $this->_coreRegistry = $registry;
        $this->httpContext = $httpContext;
        $this->_dataHelper = $dataHelper;
        $this->_productRepository = $productRepository;
        $this->_storeConfig = $context->getScopeConfig();
        $this->_filesystem = $context->getFilesystem();
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getTags()->getTitle());
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Hhmedia\Tags\Model\Tags
     */
    public function getTags()
    {
        return $this->_coreRegistry->registry('current_tags');
    }

    /**
     * Return back url for logged in and guest users
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('tags/index/index');
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

    public function getProductData($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getCount(){
        return count($this->getProductCollection('name'));
    }
    
    public function getProductCollection($sort)
    {
        if($sort == "price_high"){
            $sort = "price";
            $order = "DESC";
        }elseif($sort == "price_low"){
            $sort = "price";
            $order = "ASC";
        }elseif($sort == "position"){
            $sort = "position";
            $order = "ASC";
        }elseif($sort == "created_at"){
            $sort = "created_at";
            $order = "DESC";
        }else{
            $sort = "name";
            $order = "ASC";
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('\Hhmedia\Tags\Model\Tags');
        $productIds  =  $model->getProducts($this->getTags());

        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $productCollection->addAttributeToFilter('entity_id', array('in' => $productIds));
        $productCollection->addFieldToFilter('visibility', 4);
        $productCollection->addAttributeToSort($sort, $order);
        $productCollection->load();

        return $productCollection;
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
        $imageResized = $media->getAbsolutePath('catalog/product/tags/'.$width.'/').$image;

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

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/tags/'.$width.$image;
        return $resizedURL;
    }

    public function getMediaUrl(){
        return $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath(); 
        //return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getPlaceHolderUrl(){
        $image =  $this->_storeConfig->getValue('catalog/placeholder/small_image_placeholder');
        return '/placeholder/'.$image;
    }    

}
