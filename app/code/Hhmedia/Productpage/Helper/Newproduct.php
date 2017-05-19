<?php

namespace Hhmedia\Productpage\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Hhmedia\Editor\Model\EditorFactory;

class Newproduct extends \Magento\Framework\Url\Helper\Data
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    protected $stockItem;

    protected $_editorCollectionFactory;
    protected $editorFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem,
        \Hhmedia\Editor\Model\ResourceModel\Editor\CollectionFactory $editorCollectionFactory,
        EditorFactory $editorFactory,
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
        $this->stockItem = $stockItem;
        $this->_editorCollectionFactory = $editorCollectionFactory;
        $this->editorFactory = $editorFactory;
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
}