<?php

namespace Hhmedia\Productpage\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Hhmedia\Editor\Model\EditorFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Checkout\Helper\Cart;

class Newproduct extends \Magento\Framework\Url\Helper\Data
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    protected $stockItem;

    protected $_editorCollectionFactory;
    
    protected $editorFactory;

    protected $eavConfig;

    protected $categoryRepository;

    protected $_storeManager;

    protected $_filesystem ;
    protected $_imageFactory;

    protected $cartHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Hhmedia\Editor\Model\ResourceModel\Editor\CollectionFactory $editorCollectionFactory,
        EditorFactory $editorFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Filesystem $filesystem,         
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        Cart $cartHelper,
        TimezoneInterface $localeDate
    ) {
        $this->_filesystem = $filesystem;               
        $this->_imageFactory = $imageFactory;
        $this->localeDate = $localeDate;
        $this->stockItem = $stockItem;
        $this->_editorCollectionFactory = $editorCollectionFactory;
        $this->editorFactory = $editorFactory;
        $this->_storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->eavConfig = $eavConfig;
        $this->cartHelper = $cartHelper;
        parent::__construct($context);
    }

    public function isProductNew(ModelProduct $product)
    {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }

        return $this->localeDate->isScopeDateInInterval(
            $product->getStore(),
            $newsFromDate,
            $newsToDate
        );
    }

    public function getStockQty(ModelProduct $product)
    {
        $qty = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
        if($qty == 1){
            return true;
        }else{
            return false;
        }
    }

    public function getProductStock(ModelProduct $product,$sku)
    {
        if($product->getTypeId() == "configurable"){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $stockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            foreach ($_children as $child){
                if($child->getSku() == $sku){
                    $qty = $stockState->getStockQty($child->getId(), $product->getStore()->getWebsiteId()); 
                    return $qty;
                }else{
                    $qty = $stockState->getStockQty($child->getId(), $product->getStore()->getWebsiteId()); 
                    $stock[$child->getId()] = $qty;
                }
            }
            return max($stock);
        }else{
            $qty = $this->stockItem->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
            return $qty;
        }
    }

    public function isEditorsPick(ModelProduct $product)
    {
        $collection = $this->_editorCollectionFactory->create();
        foreach($collection as $e){
            $editor   = $this->editorFactory->create();
            if ($e->getEditorId()) {
                $editor->load($e->getEditorId());
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $model = $objectManager->create('\Hhmedia\Editor\Model\Editor');
            $products  =  $model->getProducts($editor);
            if(in_array($product->getId(), $products)){
                return true;
            }
        }
        return false;
    }

    public function isOnSale(ModelProduct $product)
    {
        $specialPriceFromDate = $product->getSpecialFromDate();
        $specialPriceToDate = $product->getSpecialToDate();
        if (!$specialPriceFromDate && !$specialPriceToDate) {
            return false;
        }

        return $this->localeDate->isScopeDateInInterval(
            $product->getStore(),
            $specialPriceFromDate,
            $specialPriceToDate
        );
    }

    public function isOneOfKind(ModelProduct $product)
    {
        $oneOfKind = $product->getData('one_of_kind');
        if($oneOfKind == 1){
            return true;
        }else{
            return false;
        }
    }

    public function isLastCall(ModelProduct $product)
    {
        $lastCall = $product->getData('last_call');
        if($lastCall == 1){
            return true;
        }else{
            return false;
        }
    }

    public function isExclusive(ModelProduct $product)
    {
        $exclusive = $product->getData('last_call');
        if($exclusive == 1){
            return true;
        }else{
            return false;
        }
    }

    function limit_review($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $shortReview = substr($text, 0, $pos[$limit]).'...';
            $fullReview = substr($text, $pos[$limit+1], end($pos));
            return "<span class='short-review'>".$shortReview."<a href='#' class='review-read-more'><span class='block'>Read More</span></a></span><span class='full-review'>".$fullReview."<a href='#' class='review-read-less'><span class='block'>Read Less</span></a></span>";
        }else{
            return "<span class='short-review'>".$text."</span>";
        }
    }

    function limit_description($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $shortDescription = substr($text, 0, $pos[$limit]).'...';
            return $shortDescription;
        }else{
            return $text;
        }
    }

    public function getColorFilter($subCatId){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $filterableAttributes = $objectManager->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

        $appState = $objectManager->get(\Magento\Framework\App\State::class);
        $layerResolver = $objectManager->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->create(\Magento\Catalog\Model\Layer\FilterList::class,
            [
                'filterableAttributes' => $filterableAttributes
            ]
        );

        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        if ($category) {
            if(isset($subCatId)){
                $categoryId = $subCatId;
            }else{
                $categoryId = $category->getId();
            }
        }else{
            $categoryId = $this->getCurrentStore()->getRootCategoryId();
        }
        
        $layer = $layerResolver->get()->setCurrentCategory($categoryId);
        $filters = $filterList->getFilters($layer);

        foreach ($filters as $filter) {
            $fname = $filter->getName();
            if($fname == "Color:"){
                foreach ($filter->getItems() as $item) {
                    $color[] =  $item->getValue();
                }
            }
        }
        return $color;
    }

    public function getPriceFilter($subCatId){
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();

        $filterableAttributes = $objectManager->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);

        $appState = $objectManager->get(\Magento\Framework\App\State::class);
        $layerResolver = $objectManager->get(\Magento\Catalog\Model\Layer\Resolver::class);
        $filterList = $objectManager->create(\Magento\Catalog\Model\Layer\FilterList::class,
            [
                'filterableAttributes' => $filterableAttributes
            ]
        );

        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        if ($category) {
            if(isset($subCatId)){
                $categoryId = $subCatId;
            }else{
                $categoryId = $category->getId();
            }
        }else{
            $categoryId = $this->getCurrentStore()->getRootCategoryId();
        }

        $layer = $layerResolver->get()->setCurrentCategory($categoryId);
        $filters = $filterList->getFilters($layer);

        foreach ($filters as $filter) {
            $fname = $filter->getName();
            if($fname == "Price"){
                foreach ($filter->getItems() as $item) {
                    $price[] =  $item->getValue();
                }
            }
        }
        return $price;
    }

    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }

    public function getSubcategories()
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
        $ids = array();
        if ($category) {
            $categoryId = $category->getId();
            $child = $category->getChildren();
            if($child != NULL){
                $subCat = explode(",", $child);
                foreach($subCat as $id){
                    $name = $this->categoryRepository->get($id, $this->_storeManager->getStore()->getId())->getName();
                    $ids[$id] = $name;
                }
            }
        }
        return $ids;
    }

    public function resize($image, $width = null, $height = null)
    {
        $media = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $absolutePath = $media->getAbsolutePath('catalog/product').$image;
        $imageResized = $media->getAbsolutePath('catalog/product/resize/'.$width.'/').$image;

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

        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/resize/'.$width.$image;
        return $resizedURL;
    }

    public function getItemDeleteUrl($item){
        return $this->cartHelper->getDeletePostJson($item);
    }

}