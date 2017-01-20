<?php

namespace MagicToolbox\MagicScroll\Helper;

/**
 * Data helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Model factory
     * @var \MagicToolbox\MagicScroll\Model\ConfigFactory
     */
    protected $_modelConfigFactory = null;

    /**
     * MagicScroll module core class
     *
     * @var \MagicToolbox\MagicScroll\Classes\MagicScrollModuleCoreClass
     *
     */
    protected $magicscroll = null;

    /**
     * @var \MagicToolbox\MagicScroll\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Product list block
     *
     * @var \Magento\Catalog\Block\Product\ListProduct
     */
    protected $listProductBlock = null;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \MagicToolbox\MagicScroll\Model\ConfigFactory $modelConfigFactory
     * @param \MagicToolbox\MagicScroll\Helper\ImageFactory $imageHelperFactory
     * @param \MagicToolbox\MagicScroll\Classes\MagicScrollModuleCoreClass $magicscroll
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \MagicToolbox\MagicScroll\Model\ConfigFactory $modelConfigFactory,
        \MagicToolbox\MagicScroll\Helper\ImageFactory $imageHelperFactory,
        \MagicToolbox\MagicScroll\Classes\MagicScrollModuleCoreClass $magicscroll,
        \Magento\Framework\Registry $registry
    ) {
        $this->_modelConfigFactory = $modelConfigFactory;
        $this->magicscroll = $magicscroll;
        $this->_imageHelper = $imageHelperFactory->create();
        $this->coreRegistry = $registry;
        parent::__construct($context);
    }

    public function getToolObj()
    {
        static $doInit = true;
        if ($doInit) {
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $collection->addFieldToFilter('status', ['neq' => 0]);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                $this->magicscroll->params->setValue($param['name'], $param['value'], $param['profile']);
            }
            $doInit = false;
        }
        return $this->magicscroll;
    }

    /**
     * Retrieve video settings data
     *
     * @return array
     */
    public function getVideoSettings()
    {
        static $videoSettingData = null;
        if ($videoSettingData === null) {
            $videoSettingData = [
                'playIfBase' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_PLAY_IF_BASE),
                'showRelated' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_SHOW_RELATED),
                'videoAutoRestart' => (int)$this->scopeConfig->getValue(\Magento\ProductVideo\Helper\Media::XML_PATH_VIDEO_AUTO_RESTART),
            ];
        }
        return $videoSettingData;
    }

    /**
     * Public method to get image sizes
     *
     * @return array
     */
    public function magicToolboxGetSizes($sizeType, $originalSizes = [])
    {
        $w = $this->magicscroll->params->getValue($sizeType.'-max-width');
        $h = $this->magicscroll->params->getValue($sizeType.'-max-height');
        if (empty($w)) {
            $w = 0;
        }
        if (empty($h)) {
            $h = 0;
        }
        if ($this->magicscroll->params->checkValue('square-images', 'No')) {
            //NOTE: fix for bad images
            if (empty($originalSizes[0]) || empty($originalSizes[1])) {
                return [$w, $h];
            }
            list($w, $h) = $this->calculateSize($originalSizes[0], $originalSizes[1], $w, $h);
        } else {
            $h = $w = $h ? ($w ? min($w, $h) : $h) : $w;
        }
        return [$w, $h];
    }

    /**
     * Public method to calculate sizes
     *
     * @return array
     */
    private function calculateSize($originalW, $originalH, $maxW = 0, $maxH = 0)
    {
        if (!$maxW && !$maxH) {
            return [$originalW, $originalH];
        } elseif (!$maxW) {
            $maxW = ($maxH * $originalW) / $originalH;
        } elseif (!$maxH) {
            $maxH = ($maxW * $originalH) / $originalW;
        }
        $sizeDepends = $originalW/$originalH;
        $placeHolderDepends = $maxW/$maxH;
        if ($sizeDepends > $placeHolderDepends) {
            $newW = $maxW;
            $newH = $originalH * ($maxW / $originalW);
        } else {
            $newW = $originalW * ($maxH / $originalH);
            $newH = $maxH;
        }
        return [round($newW), round($newH)];
    }

    /**
     * Get HTML data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @param array $mediaAttributeCodes
     * @return string
     */
    public function getHtmlData($product, $isAssociatedProduct = false, $mediaAttributeCodes = ['small_image'])
    {
        static $_html = [];
        $id = $product->getId();
        $key = implode('_', $mediaAttributeCodes);
        if (!isset($_html[$key])) {
            $_html[$key] = [];
        }
        $html = & $_html[$key];
        if (!isset($html[$id])) {
            $this->magicscroll->params->setProfile('category');

            /** @var $listProductBlock \Magento\Catalog\Block\Product\ListProduct */
            $listProductBlock = $this->getListProductBlock();
            if ($listProductBlock) {
                $productImage = $listProductBlock->getImage($product, $listProductBlock->getMode() == 'grid' ? 'category_page_grid' : 'category_page_list');
                $productImageWidth = $productImage->getWidth();
            } else {
                list($productImageWidth, ) = $this->magicToolboxGetSizes('thumb');
            }


            $images = $this->getGalleryData($product, $isAssociatedProduct);
            if (!count($images)) {
                $html[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                return $html[$id];
            }

            $html[$id] = $this->magicscroll->getMainTemplate($images, ['id' => "MagicScroll-category-{$id}"]);
            $html[$id] = '<div class="MagicToolboxContainer" style="width: '.$productImageWidth.'px;">'.$html[$id].'</div>';
        }

        return $html[$id];
    }

    /**
     * Retrieve another renderer
     *
     * @return mixed
     */
    public function getAnotherRenderer()
    {
        $data = $this->coreRegistry->registry('magictoolbox_category');
        if ($data) {
            $skip = true;
            foreach ($data['renderers'] as $name => $renderer) {
                if ($name == 'configurable.magicscroll') {
                    $skip = false;
                    continue;
                }
                if ($skip) {
                    continue;
                }
                if ($renderer) {
                    return $renderer;
                }
            }
        }
        return null;
    }

    /**
     * Get placeholder HTML
     *
     * @return string
     */
    public function getPlaceholderHtml()
    {
        static $html = null;
        if ($html === null) {
            $placeholderUrl = $this->_imageHelper->getDefaultPlaceholderUrl('small_image');
            list($width, $height) = $this->magicToolboxGetSizes('thumb');
            $html = '<div class="MagicToolboxContainer placeholder" style="width: '.$width.'px;height: '.$height.'px">'.
                    '<span class="align-helper"></span>'.
                    '<img src="'.$placeholderUrl.'"/>'.
                    '</div>';
        }
        return $html;
    }

    /**
     * Set product list block
     *
     */
    public function setListProductBlock(\Magento\Catalog\Block\Product\ListProduct $block)
    {
        $this->listProductBlock = $block;
    }

    /**
     * Get product list block
     *
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    public function getListProductBlock()
    {
        return $this->listProductBlock;
    }

    /**
     * Retrieve gallery data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @return array
     */
    public function getGalleryData($product, $isAssociatedProduct)
    {
        static $images = [];
        $id = $product->getId();
        if (!isset($images[$id])) {
            $images[$id] = [];
            $link = $this->magicscroll->params->checkValue('link-to-product-page', 'Yes');
            //TODO: get parent product link for associated products
            $link = $link && !$isAssociatedProduct ? $product->getProductUrl() : false;
            $smallImage = $product->getSmallImage();
            if ($smallImage && 'no_selection' != $smallImage) {
                $images[$id][] = [
                    'title' => $product->getSmallImageLabel(),
                    'file' => $smallImage,
                    'link' => $link,
                ];
            }

            $mediaGalleryImages = $product->getMediaGalleryImages();
            if ($mediaGalleryImages === null) {
                $product->load($id);
                $mediaGalleryImages = $product->getMediaGalleryImages();
            }
            if ($mediaGalleryImages instanceof \Magento\Framework\Data\Collection) {
                foreach ($mediaGalleryImages as $image) {
                    /* @var \Magento\Framework\DataObject $image */
                    $mediaType = $image->getMediaType();
                    if ($mediaType != 'image') {
                        continue;
                    }
                    if ($smallImage == $image->getFile()) {
                        continue;
                    }
                    $images[$id][] = [
                        'title' => $image->getLabel(),
                        'file' => $image->getFile(),
                        'link' => $link,
                    ];
                }
            }

            foreach ($images[$id] as $key => $image) {

                $img = $this->_imageHelper->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                        ->setImageFile($image['file'])
                        ->getUrl();

                $originalSizeArray = $this->_imageHelper->getOriginalSizeArray();

                list($w, $h) = $this->magicToolboxGetSizes('thumb', $originalSizeArray);
                $medium = $this->_imageHelper->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                        ->setImageFile($image['file'])
                        ->getUrl();
                $images[$id][$key]['img'] = $medium;
            }
        }
        return $images[$id];
    }

    /**
     * Public method for retrieve statuses
     *
     * @return array
     */
    public function getStatuses($profile = false, $force = false)
    {
        static $result = null;
        if (is_null($result) || $force) {
            $result = [];
            $model = $this->_modelConfigFactory->create();
            $collection = $model->getCollection();
            $collection->addFieldToFilter('platform', 0);
            $data = $collection->getData();
            foreach ($data as $key => $param) {
                if (!isset($result[$param['profile']])) {
                    $result[$param['profile']] = [];
                }
                $result[$param['profile']][$param['name']] = $param['status'];
            }
        }
        return isset($result[$profile]) ? $result[$profile] : $result;
    }

    /**
     * Public method for retrieve config map
     *
     * @return array
     */
    public function getConfigMap()
    {
        return unserialize('a:3:{s:7:"default";a:4:{s:7:"General";a:1:{i:0;s:28:"include-headers-on-all-pages";}s:24:"Positioning and Geometry";a:3:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:13:"square-images";}s:6:"Scroll";a:16:{i:0;s:5:"width";i:1;s:6:"height";i:2;s:11:"orientation";i:3;s:4:"mode";i:4;s:5:"items";i:5;s:5:"speed";i:6;s:8:"autoplay";i:7;s:4:"loop";i:8;s:4:"step";i:9;s:6:"arrows";i:10;s:10:"pagination";i:11;s:6:"easing";i:12;s:13:"scrollOnWheel";i:13;s:9:"lazy-load";i:14;s:19:"scroll-extra-styles";i:15;s:16:"show-image-title";}s:13:"Miscellaneous";a:1:{i:0;s:20:"link-to-product-page";}}s:7:"product";a:3:{s:7:"General";a:1:{i:0;s:13:"enable-effect";}s:24:"Positioning and Geometry";a:3:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:13:"square-images";}s:6:"Scroll";a:16:{i:0;s:5:"width";i:1;s:6:"height";i:2;s:11:"orientation";i:3;s:4:"mode";i:4;s:5:"items";i:5;s:5:"speed";i:6;s:8:"autoplay";i:7;s:4:"loop";i:8;s:4:"step";i:9;s:6:"arrows";i:10;s:10:"pagination";i:11;s:6:"easing";i:12;s:13:"scrollOnWheel";i:13;s:9:"lazy-load";i:14;s:19:"scroll-extra-styles";i:15;s:16:"show-image-title";}}s:8:"category";a:4:{s:7:"General";a:1:{i:0;s:13:"enable-effect";}s:24:"Positioning and Geometry";a:3:{i:0;s:15:"thumb-max-width";i:1;s:16:"thumb-max-height";i:2;s:13:"square-images";}s:6:"Scroll";a:16:{i:0;s:5:"width";i:1;s:6:"height";i:2;s:11:"orientation";i:3;s:4:"mode";i:4;s:5:"items";i:5;s:5:"speed";i:6;s:8:"autoplay";i:7;s:4:"loop";i:8;s:4:"step";i:9;s:6:"arrows";i:10;s:10:"pagination";i:11;s:6:"easing";i:12;s:13:"scrollOnWheel";i:13;s:9:"lazy-load";i:14;s:19:"scroll-extra-styles";i:15;s:16:"show-image-title";}s:13:"Miscellaneous";a:1:{i:0;s:20:"link-to-product-page";}}}');
    }
}
