<?php

/**
 * Magic Scroll view block
 *
 */
namespace MagicToolbox\MagicScroll\Block\Product\View;

use Magento\Framework\Data\Collection;
use MagicToolbox\MagicScroll\Helper\Data;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{
    /**
     * Helper
     *
     * @var \MagicToolbox\MagicScroll\Helper\Data
     */
    public $magicToolboxHelper = null;

    /**
     * MagicScroll module core class
     *
     * @var \MagicToolbox\MagicScroll\Classes\MagicScrollModuleCoreClass
     *
     */
    public $toolObj = null;

    /**
     * @var \MagicToolbox\MagicScroll\Helper\Image
     */
    protected $_imageHelper;

    /**
     * Rendered gallery HTML
     * @var array
     */
    protected $renderedGalleryHtml = [];

    /**
     * ID of the current product
     * @var integer
     */
    protected $currentProductId = null;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \MagicToolbox\MagicScroll\Helper\Data $magicToolboxHelper
     * @param \MagicToolbox\MagicScroll\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \MagicToolbox\MagicScroll\Helper\Data $magicToolboxHelper,
        \MagicToolbox\MagicScroll\Helper\Image $imageHelper,
        array $data = []
    ) {
        $this->magicToolboxHelper = $magicToolboxHelper;
        $this->toolObj = $this->magicToolboxHelper->getToolObj();
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
        $this->_imageHelper = $imageHelper;
    }

    /**
     * Get escaper
     *
     * @return \Magento\Framework\Escaper
     */
    public function getEscaper()
    {
        return $this->_escaper;
    }

    /**
     * Retrieve collection of gallery images
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return Magento\Framework\Data\Collection
     */
    public function getGalleryImagesCollection($product = null)
    {
        static $images = [];
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $id = $product->getId();
        if (!isset($images[$id])) {
            $images[$id] = $product->getMediaGalleryImages();
            if ($images[$id] instanceof \Magento\Framework\Data\Collection) {
                foreach ($images[$id] as $image) {
                    /* @var \Magento\Framework\DataObject $image */

                    $mediaType = $image->getMediaType();
                    if ($mediaType != 'image') {
                        continue;
                    }

                    $img = $this->_imageHelper->init($product, 'product_page_image_large', ['width' => null, 'height' => null])
                            ->setImageFile($image->getFile())
                            ->getUrl();

                    $originalSizeArray = $this->_imageHelper->getOriginalSizeArray();

                    if ($mediaType == 'image') {

                        list($w, $h) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb', $originalSizeArray);
                        $medium = $this->_imageHelper->init($product, 'product_page_image_medium', ['width' => $w, 'height' => $h])
                                ->setImageFile($image->getFile())
                                ->getUrl();
                        $image->setData('medium_image_url', $medium);
                    }
                }
            }
        }
        return $images[$id];
    }

    /**
     * Retrieve original gallery block
     *
     * @return mixed
     */
    public function getOriginalBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? null : $data['blocks']['product.info.media.image'];
    }

    /**
     * Retrieve another gallery block
     *
     * @return mixed
     */
    public function getAnotherBlock()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        if ($data) {
            $skip = true;
            foreach ($data['blocks'] as $name => $block) {
                if ($name == 'product.info.media.magicscroll') {
                    $skip = false;
                    continue;
                }
                if ($skip) {
                    continue;
                }
                if ($block) {
                    return $block;
                }
            }
        }
        return null;
    }

    /**
     * Check for installed modules, which can operate in cooperative mode
     *
     * @return bool
     */
    public function isCooperativeModeAllowed()
    {
        $data = $this->_coreRegistry->registry('magictoolbox');
        return is_null($data) ? false : $data['cooperative-mode'];
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->renderGalleryHtml();
        return parent::_beforeToHtml();
    }

    /**
     * Get rendered HTML
     *
     * @param integer $id
     * @return string
     */
    public function getRenderedHtml($id = null)
    {
        if (is_null($id)) {
            $id = $this->getProduct()->getId();
        }
        return isset($this->renderedGalleryHtml[$id]) ? $this->renderedGalleryHtml[$id] : '';
    }

    /**
     * Render gallery block HTML
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isAssociatedProduct
     * @param array $data
     * @return $this
     */
    public function renderGalleryHtml($product = null, $isAssociatedProduct = false, $data = [])
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        $this->currentProductId = $id = $product->getId();
        if (!isset($this->renderedGalleryHtml[$id])) {
            $this->toolObj->params->setProfile('product');
            $name = $product->getName();
            $magicscrollData = [];

            $images = $this->getGalleryImagesCollection($product);

            $originalBlock = $this->getOriginalBlock();

            if (!$images->count()) {
                $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                return $this;
            }

            foreach ($images as $image) {

                $mediaType = $image->getMediaType();
                $isImage = $mediaType == 'image';
                $isVideo = $mediaType == 'external-video';

                if (!$isImage) {
                    continue;
                }

                $label = $isImage ? $image->getLabel() : $image->getVideoTitle();
                if (empty($label)) {
                    $label = $name;
                }

                $magicscrollData[] = [
                    'title' => $label,
                    'img' => $image->getData('medium_image_url'),
                ];
            }
            if (empty($magicscrollData)) {
                if ($originalBlock) {
                    $this->renderedGalleryHtml[$id] = $isAssociatedProduct ? '' : $this->getPlaceholderHtml();
                }
                return $this;
            }

            $this->renderedGalleryHtml[$id] = $this->toolObj->getMainTemplate($magicscrollData, ['id' => "MagicScroll-product-{$id}"]);

            $this->renderedGalleryHtml[$id] = '<div class="MagicToolboxContainer">'.$this->renderedGalleryHtml[$id].'</div>';

        }
        return $this;
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
            $placeholderUrl = $this->_imageHelper->getDefaultPlaceholderUrl('image');
            list($width, $height) = $this->magicToolboxHelper->magicToolboxGetSizes('thumb');
            $html = '<div class="MagicToolboxContainer placeholder" style="width: '.$width.'px;height: '.$height.'px">'.
                    '<span class="align-helper"></span>'.
                    '<img src="'.$placeholderUrl.'"/>'.
                    '</div>';
        }
        return $html;
    }
}
