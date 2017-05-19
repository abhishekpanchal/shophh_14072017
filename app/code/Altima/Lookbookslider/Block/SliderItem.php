<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Block;

use Altima\Lookbookslider\Model\Slider as SliderModel;
use Altima\Lookbookslider\Model\Status;
use Hhmedia\Productpage\Helper\Newproduct as HelperData;

class SliderItem extends \Magento\Framework\View\Element\Template {

    const STYLESLIDE_EVOLUTION_TEMPLATE      = 'Altima_Lookbookslider::slider/evolution.phtml';
    const STYLESLIDE_POPUP_TEMPLATE          = 'Altima_Lookbookslider::slider/popup.phtml';
    const STYLESLIDE_SPECIAL_NOTE_TEMPLATE   = 'Altima_Lookbookslider::slider/special/note.phtml';
    const STYLESLIDE_FLEXSLIDER_TEMPLATE     = 'Altima_Lookbookslider::slider/flexslider.phtml';
    const STYLESLIDE_LOOKBOOKSLIDER_TEMPLATE = 'Altima_Lookbookslider::slider/lookbookslider.phtml';
    const STYLESLIDE_CUSTOM_TEMPLATE         = 'Altima_Lookbookslider::slider/custom.phtml';

    protected $_stdlibDateTime;
    protected $_storeManager;
    protected $_sliderFactory;
    protected $_slider;
    protected $_sliderId;
    protected $_lookbooksliderHelper;
    protected $_slideCollectionFactory;
    protected $_scopeConfig;
    protected $_stdTimezone;
    protected $imageHelper;
    protected $_cartHelper;
    protected $directory_list;
    protected $_productRepository;
    protected $helperData;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
            \Altima\Lookbookslider\Model\ResourceModel\Slide\CollectionFactory $slideCollectionFactory,
            \Altima\Lookbookslider\Model\SliderFactory $sliderFactory,
            SliderModel $slider,
            \Magento\Framework\Stdlib\DateTime\DateTime $stdlibDateTime,
            \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper,
            \Magento\Framework\Stdlib\DateTime\Timezone $_stdTimezone,
            \Magento\Catalog\Model\ProductFactory $productFactory,
            \Magento\Catalog\Model\ProductRepository $productRepository,
            \Magento\Catalog\Helper\Image $imageHelper,
            HelperData $helperData,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_sliderFactory          = $sliderFactory;
        $this->_slider                 = $slider;
        $this->_stdlibDateTime         = $stdlibDateTime;
        $this->_lookbooksliderHelper   = $lookbooksliderHelper;
        $this->_storeManager           = $context->getStoreManager();
        $this->_slideCollectionFactory = $slideCollectionFactory;
        $this->_scopeConfig            = $context->getScopeConfig();
        $this->_stdTimezone            = $_stdTimezone;
        $this->_productFactory         = $productFactory;
        $this->_productRepository = $productRepository;
        $this->imageHelper             = $imageHelper;
        $this->directory_list          = $directory_list;
        $this->helperData    = $helperData;
    }

    protected function _toHtml() {
        $store = $this->_storeManager->getStore()->getId();
        return parent::_toHtml();
    }

    public function setSliderId($sliderId) {
        $this->_sliderId = $sliderId;
        $slider          = $this->_sliderFactory->create()->load($this->_sliderId);
        if ($slider->getSliderId()) {
            $this->setSlider($slider);
            if ($slider->getStyleContent() == SliderModel::STYLE_CONTENT_NO) {
                $this->setTemplate(SliderModel::STYLESLIDE_CUSTOM_TEMPLATE);
            } else {
                $this->setStyleSlideTemplate($slider->getStyleSlide());
            }
        }
        return $this;
    }

    public function setStyleSlideTemplate($styleSlideId) {
        switch ($styleSlideId) {
            //Evolution slide
            case SliderModel::STYLESLIDE_EVOLUTION_ONE:
            case SliderModel::STYLESLIDE_EVOLUTION_TWO:
            case SliderModel::STYLESLIDE_EVOLUTION_THREE:
            case SliderModel::STYLESLIDE_EVOLUTION_FOUR:
                $this->setTemplate(self::STYLESLIDE_EVOLUTION_TEMPLATE);
                break;
            case SliderModel::STYLESLIDE_POPUP:
                $this->setTemplate(self::STYLESLIDE_POPUP_TEMPLATE);
                break;
            //Note all page
            case SliderModel::STYLESLIDE_SPECIAL_NOTE:
                $this->setTemplate(self::STYLESLIDE_SPECIAL_NOTE_TEMPLATE);
                break;
            // Lookbookslider slide
            default:
                $this->setTemplate(self::STYLESLIDE_LOOKBOOKSLIDER_TEMPLATE);
                break;
        }
    }

    public function isShowTitle() {
        return $this->_slider->getShowTitle() == SliderModel::SHOW_TITLE_YES ? TRUE : FALSE;
    }

    public function getSlideCollection() {
        $storeViewId     = $this->_storeManager->getStore()->getId();
        $dateTimeNow     = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $slideCollection = $this->_slideCollectionFactory->create()
                ->setStoreViewId($storeViewId)
                ->addFieldToFilter('slider_id', $this->_slider->getId())
                ->addFieldToFilter('is_active', Status::STATUS_ENABLED)
                ->setOrder('position', 'ASC');
        if ($this->_slider->getSortType() == SliderModel::SORT_TYPE_RANDOM) {
            $slideCollection->setOrderRandBySlideId();
        }
        return $slideCollection;
    }

    public function getSlide($sliderId,$slideId) {
        $storeViewId     = $this->_storeManager->getStore()->getId();
        $dateTimeNow     = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $slide = $this->_slideCollectionFactory->create()
                ->setStoreViewId($storeViewId)
                ->addFieldToFilter('slider_id', $sliderId)
                ->addFieldToFilter('slide_id', $slideId)
                ->addFieldToFilter('is_active', Status::STATUS_ENABLED)
                ->setOrder('position', 'ASC');
        return $slide;
    }

    public function getShotCollection($sliderId) {
        $storeViewId     = $this->_storeManager->getStore()->getId();
        $dateTimeNow     = $this->_stdTimezone->date()->format('Y-m-d H:i:s');
        $shotCollection = $this->_slideCollectionFactory->create()
                ->setStoreViewId($storeViewId)
                ->addFieldToFilter('slider_id', $sliderId)
                ->addFieldToFilter('is_active', Status::STATUS_ENABLED)
                ->setOrder('position', 'ASC');
        return $shotCollection;
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

    public function getFirstSlideItem() {
        $sliderItem = $this->getSlideCollection()
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem();
        return $sliderItem;
    }

    public function getPositionNote() {
        return $this->_slider->getPositionNoteCode();
    }

    public function setSlider(\Altima\Lookbookslider\Model\Slider $slider) {
        $this->_slider = $slider;
        return $this;
    }

    public function getSlider() {
        return $this->_slider;
    }

    public function getSlideImageUrl(\Altima\Lookbookslider\Model\Slide $slide) {
        $srcImg = $this->_lookbooksliderHelper->getBaseUrlMedia($slide->getImage_path());
        //$width  = $this->_slider->getWidth();
        //$height = $this->_slider->getHeight();
        $width = 1100;
        $height = 640;
        return $this->_lookbooksliderHelper->getResizedUrl($slide->getImage_path(), $width, $height);
    }

    public function getSlideThumbUrl(\Altima\Lookbookslider\Model\Slide $slide) {
        $srcImg = $this->_lookbooksliderHelper->getBaseUrlMedia($slide->getImage_path());
        $width  = $this->_slider->getWidth();
        $width  = $width / 5;
        $height = 100;
        return $this->_lookbooksliderHelper->getResizedUrl($slide->getImage_path(), $width, $height);
    }

    public function getFlexsliderHtmlId() {
        return 'altima-lookbookslider-slider-' . $this->getSlider()->getId() . $this->_stdlibDateTime->gmtTimestamp();
    }

    public function getProducts($slide) {
        $hotspots          = $slide->getHotspots();
        if ($hotspots == '')
            return '';
        $decoded_array     = json_decode($hotspots, true);
        foreach ($decoded_array as $key => $value) {
            $sku[] = $decoded_array[$key]['sku'];
        }
        return $sku;
    }

    public function getProductCollection($slide) {
        $hotspots          = $slide->getHotspots();
        if ($hotspots == '')
            return '';
        $decoded_array     = json_decode($hotspots, true);
        foreach ($decoded_array as $key => $value) {
            $product_details = null;
            if ($decoded_array[$key]['sku'] != '') {
                $product         = $this->_productFactory->create();
                $product_details = $product->loadByAttribute('sku', $decoded_array[$key]['sku']);
                if ($product_details) {
                    $product_details_full = $product->load($product_details->getId());
                }
            }
            $html_content = '';
            $html_content .= '<div class="product-info-main" style="';
            //$html_content .= 'left:' . round($value['width'] / 2) . 'px;';
            //$html_content .= 'top:' . round($value['height'] / 2) . 'px;';

            if ($product_details) {
                $_p_name = $product_details->getName();

                if ($this->_lookbooksliderHelper->canShowProductDescr()) {
                    $_p_shrt_desc  = $product_details_full->getDescription();
                    $_p_shrt_desc  = strip_tags($_p_shrt_desc);
                    $_p_shrt_desc  = substr($_p_shrt_desc, 0, 120);
                    $_p_shrt_desc  = rtrim($_p_shrt_desc, "!,.-");
                    $_p_shrt_desc  = substr($_p_shrt_desc, 0, strrpos($_p_shrt_desc, ' '));
                    $_p_shrt_desc  = $_p_shrt_desc . '...';
                    $_p_shrt_image = $this->imageHelper->init($product, 'product_small_image')->keepAspectRatio(true)->resize(400, 400)->getUrl();
                }
                //$html_content .= 'width: ' . strlen($_p_name) * 8 . 'px;';
            } else {
                //$html_content .= 'width: ' . strlen($decoded_array[$key]['text']) * 8 . 'px;';
            }
            $html_content .= '"><div class="pro-detail-div">';

            if ($product_details) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
                $priceHelper   = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
                $_p_price      = $product_details->getFinalPrice();
                $_p_price      = $priceHelper->currency($_p_price, true, false);
                $html_content .= '<div class="left-detail">';
                if ($this->_lookbooksliderHelper->canShowProductDescr()) {

                    $_p_url = $product_details->getProductUrl();
                    $html_content .= '<div class="image"><a href=\'' . $_p_url . '\'><img src="' . $_p_shrt_image . '" alt="product image"/></a></div>';
                }
                $quickViewUrl = $this->getUrl('').'weltpixel_quickview/catalog_product/view/id/'.$product_details->getId();
                
                $html_content .= '<a href="javascript: void(0);" data-quickview-url="'.$quickViewUrl.'" class="weltpixel-quickview quickview-lookbook" title="Quick View">Quick Look</a>';

                if ($product_details->isAvailable()) {
                    if ($this->_lookbooksliderHelper->getUseFullProdUrl()) {
                        $_p_url = $product_details->getProductUrl();
                    } else {
                        $_p_url = $product_details->getProductUrl();
                    }
                    $html_content .= '<div class="product attribute name"><a href=\'' . $_p_url . '\' class="hover-effect" target="_blank">' . $_p_name . '</a></div>';
                } else {
                    $html_content .= '<h2>' . $_p_name . '</h2>';
                    $html_content .= '<div class="out-of-stock"><span>' . __('Out of stock') . '</span></div>';
                }

                if($this->helperData->getStockQty($product_details)){
                    $html_content .= '<div class="one-left">' . __('Only 1 left') . '</div>';
                }elseif($this->helperData->isEditorsPick($product_details)){
                    $html_content .= '<div class="editors-pick">' . __('Editor’s Pick') . '</div>';
                }elseif($this->helperData->isExclusive($product_details)){
                    $html_content .= '<div class="exclusive">' . __('H&H exclusive') . '</div>';
                }elseif($this->helperData->isOnSale($product_details)){
                    $html_content .= '<div class="one-sale">' . __('On Sale') . '</div>';
                }elseif($this->helperData->isOneOfKind($product_details)){
                    $html_content .= '<div class="one-kind">' . __('One of a Kind') . '</div>';
                }elseif($this->helperData->isProductNew($product_details)){
                    $html_content .= '<div class="new-product">' . __('New') . '</div>';
                }elseif($this->helperData->isLastCall($product_details)){
                    $html_content .= '<div class="last-call">' . __('Last Call') . '</div>';
                }

                //$html_content .= '<div class="desc">' . $_p_shrt_desc . '</div>';
                if ($product_details->getFinalPrice()) {
                    if ($product_details->getPrice() > $product_details->getFinalPrice()) {
                        $regular_price = $product_details->getPrice();
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
                        $priceHelper   = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
                        $regular_price = $priceHelper->currency($regular_price, true, false);
                        $_p_price      = '<span class="current-price">' . $_p_price . '</span>' . '<span class="old-price">' . $regular_price . '</span>';
                    }
                    $html_content .= '<div class="price">' . $_p_price . '</div>';
                }
                /*if ($this->_lookbooksliderHelper->canShowAddToCart()) {
                    $html_content .= $this->getAddToCartHtml($product_details_full);
                }*/
                $html_content .= '</div>';
            }
            $html_content .= '</div></div>';
            $decoded_array[$key]['text'] = $html_content;
        }
        $result = $decoded_array;
        return $result;
    }

    public function getHotspotsWithProductDetails($slide) {
        $hotspots          = $slide->getHotspots();
        if ($hotspots == '')
            return '';
        $decoded_array     = json_decode($hotspots, true);
        $img_width         = $this->_slider->getWidth();
        $hotspot_icon      = $this->_lookbooksliderHelper->getHotspotIcon();
        if (!$hotspot_icon)
            $hotspot_icon      = $this->getViewFileUrl('Altima_Lookbookslider::images/hotspot-icon.png');
        $hotspot_icon_path = $this->_lookbooksliderHelper->getHotspotIconPath();
        if (!$hotspot_icon_path) {
            $icon_dimensions['width']  = 30;
            $icon_dimensions['height'] = 30;
        } else {
            $icon_dimensions = $this->_lookbooksliderHelper->getImageDimensions($hotspot_icon_path);
        }
        foreach ($decoded_array as $key => $value) {
            $product_details = null;
            if ($decoded_array[$key]['sku'] != '') {
                $product         = $this->_productFactory->create();
                $product_details = $product->loadByAttribute('sku', $decoded_array[$key]['sku']);
                if ($product_details) {
                    $product_details_full = $product->load($product_details->getId());
                } else {
                    $decoded_array[$key]['text'] = __("Product with SKU %s doesn't exist", $decoded_array[$key]['sku']);
                    $product_details_full        = NULL;
                }
            }
            $html_content = '';
            if (!isset($icon_dimensions['error'])) {
                $html_content .= '<i class="ion-android-search hotspot-inactive" style="
                        left:' . (round($value['width'] / 2) - round($icon_dimensions['width'] / 2)) . 'px; 
                        top:' . (round($value['height'] / 2) - round($icon_dimensions['height'] / 2)) . 'px;
                        "/></i>';
                // $html_content .= '<img class="hotspot-icon" src="' . $hotspot_icon . '" alt="" style="
                //         left:' . (round($value['width'] / 2) - round($icon_dimensions['width'] / 2)) . 'px; 
                //         top:' . (round($value['height'] / 2) - round($icon_dimensions['height'] / 2)) . 'px;
                //         "/>';
                $decoded_array[$key]['icon_width']  = $icon_dimensions['width'];
                $decoded_array[$key]['icon_height'] = $icon_dimensions['height'];
            }
            $decoded_array[$key]['number'] = $decoded_array[$key]['text'];
            $html_content .= '<div class="product-info" style="';
            $html_content .= 'left:' . round($value['width'] / 2) . 'px;';
            $html_content .= 'top:' . round($value['height'] / 2) . 'px;';

            if ($product_details) {
                $_p_name = $product_details->getName();

                if ($this->_lookbooksliderHelper->canShowProductDescr()) {
                    $_p_shrt_desc  = $product_details_full->getDescription();
                    $_p_shrt_desc  = strip_tags($_p_shrt_desc);
                    $_p_shrt_desc  = substr($_p_shrt_desc, 0, 120);
                    $_p_shrt_desc  = rtrim($_p_shrt_desc, "!,.-");
                    $_p_shrt_desc  = substr($_p_shrt_desc, 0, strrpos($_p_shrt_desc, ' '));
                    $_p_shrt_desc  = $_p_shrt_desc . '...';
                    $_p_shrt_image = $this->imageHelper->init($product, 'product_small_image')->keepAspectRatio(true)->resize(400, 400)->getUrl();
                }
                $html_content .= 'width: ' . strlen($_p_name) * 8 . 'px;';
            } else {
                $html_content .= 'width: ' . strlen($decoded_array[$key]['text']) * 8 . 'px;';
            }
            $html_content .= '"><div class="pro-detail-div">';


            if ($product_details) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
                $priceHelper   = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
                $_p_price      = $product_details->getFinalPrice();
                $_p_price      = $priceHelper->currency($_p_price, true, false);
                $html_content .= '<div class="left-detail">';
                
                if ($this->_lookbooksliderHelper->canShowProductDescr()) {
                    $_p_url = $product_details->getProductUrl();
                    $html_content .= '<div class="image"><a href=\'' . $_p_url . '\'><img src="' . $_p_shrt_image . '" alt="product image"/></a></div>';
                }
                if ($product_details->isAvailable()) {
                    if ($this->_lookbooksliderHelper->getUseFullProdUrl()) {
                        $_p_url = $product_details->getProductUrl();
                    } else {
                        $_p_url = $product_details->getProductUrl();
                    }
                    $html_content .= '<h2><a href=\'' . $_p_url . '\' target="_blank">' . $_p_name . '</a></h2>';
                } else {
                    $html_content .= '<h2>' . $_p_name . '</h2>';
                    $html_content .= '<div class="out-of-stock"><span>' . __('Out of stock') . '</span></div>';
                }

                if($this->helperData->getStockQty($product_details)){
                    $html_content .= '<div class="one-left">' . __('Only 1 left') . '</div>';
                }elseif($this->helperData->isEditorsPick($product_details)){
                    $html_content .= '<div class="editors-pick">' . __('Editor’s Pick') . '</div>';
                }elseif($this->helperData->isExclusive($product_details)){
                    $html_content .= '<div class="exclusive">' . __('H&H exclusive') . '</div>';
                }elseif($this->helperData->isOnSale($product_details)){
                    $html_content .= '<div class="one-sale">' . __('On Sale') . '</div>';
                }elseif($this->helperData->isOneOfKind($product_details)){
                    $html_content .= '<div class="one-kind">' . __('One of a Kind') . '</div>';
                }elseif($this->helperData->isProductNew($product_details)){
                    $html_content .= '<div class="new-product">' . __('New') . '</div>';
                }elseif($this->helperData->isLastCall($product_details)){
                    $html_content .= '<div class="last-call">' . __('Last Call') . '</div>';
                }

                $html_content .= '<div class="desc">' . $_p_shrt_desc . '</div>';
                if ($product_details->getFinalPrice()) {
                    if ($product_details->getPrice() > $product_details->getFinalPrice()) {
                        $regular_price = $product_details->getPrice();
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of Object Manager
                        $priceHelper   = $objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
                        $regular_price = $priceHelper->currency($regular_price, true, false);
                        $_p_price      =  '<span class="current-price">' . $_p_price . '</span>' . '<span class="old-price">' . $regular_price . '</span>';
                    }
                    $html_content .= '<div class="price">' . $_p_price . '</div>';
                }
                if ($this->_lookbooksliderHelper->canShowAddToCart()) {
                    $html_content .= $this->getAddToCartHtml($product_details_full);
                }
                $html_content .= '</div>';
            } else {
                $html_content .= '<div>Product with SKU "'.$decoded_array[$key]['text'].'" doesn\'t exists.</div>';
                //$html_content .= '<div><a href=\'' . $decoded_array[$key]['href'] . '\'>' . $decoded_array[$key]['text'] . '</a></div>';
            }
            $html_content .= '</div></div>';
            $decoded_array[$key]['text'] = $html_content;
        }
        $result = $decoded_array;
        return $result;
    }

    public function getAddToCartHtml($product) {
        $html = '';
        if ($product && $product->isSaleable()) {
            $block = $this->getLayout()->createBlock('Altima\Lookbookslider\Block\Addtocart')
                    ->setTemplate('Altima_Lookbookslider::slider/addtocart.phtml')
                    ->setProduct($product);
            $html .= $block->toHtml();
        }
        return $html;
    }

}
