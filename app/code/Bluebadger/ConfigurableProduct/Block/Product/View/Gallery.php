<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-06
 * Time: 15:18
 */

namespace Bluebadger\ConfigurableProduct\Block\Product\View;

use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProvider;
use Magento\Framework\Json\EncoderInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\NoSuchEntityException;

class Gallery extends \Magento\Catalog\Block\Product\View\Gallery
{

    protected $productRepository;

    protected $configurableOptionsProvider;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        ProductRepository $productRepository,
        ConfigurableOptionsProvider $configurableOptionsProvider,
        array $data = []
    ) {
        $this->productRepository = $productRepository;
        $this->configurableOptionsProvider = $configurableOptionsProvider;

        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }


    public function getGalleryImages()
    {
        $product = $this->getProduct();
        if ($product->getTypeId() == "configurable") {
            $product = $this->getSelectedProduct($product);
            $images = $product->getMediaGalleryImages();
            if ($images instanceof \Magento\Framework\Data\Collection) {
                foreach ($images as $image) {
                    /* @var \Magento\Framework\DataObject $image */
                    $image->setData(
                        'small_image_url',
                        $this->_imageHelper->init($product, 'product_page_image_small')
                            ->setImageFile($image->getFile())
                            ->getUrl()
                    );
                    $image->setData(
                        'medium_image_url',
                        $this->_imageHelper->init($product, 'product_page_image_medium')
                            ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                            ->setImageFile($image->getFile())
                            ->getUrl()
                    );
                    $image->setData(
                        'large_image_url',
                        $this->_imageHelper->init($product, 'product_page_image_large')
                            ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                            ->setImageFile($image->getFile())
                            ->getUrl()
                    );
                }
            }

            return $images;
        }
        return parent::getGalleryImages();
    }

    public function getSelectedProduct(Product $product)
    {

        $applyAttributes = ['color',"primary_color"];

        if ($product->getTypeId() == "configurable") {

            /** @var EngineFilter $fe */
            $configurables = $this->configurableOptionsProvider->getProducts($product);
            $params = $this->getRequest()->getParams();
            foreach ( $params as $attributeCode => $value) {
                if (in_array(   $attributeCode , $applyAttributes )) {
                    foreach($configurables as $index => &$_variant) {
                        // Might need to explode value on _
                        if ( !in_array ( $_variant->getData($attributeCode),  [$value]) ) {
                            unset($configurables[$index]);
                            continue;
                        }
                    }
                }
            }

            if (count($configurables) && count($params)) {
                if (count($configurables) == 1) {
                    return array_shift($configurables);
                }
            }


            $productId = $product->getData("default_simple_product");
            try {
                $defaultProduct = $this->productRepository->getById($productId);
                if ($defaultProduct->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED ) {
                    return $defaultProduct;
                } else {
                    if (count($configurables)) {
                        $sortedConfigurables = [];
                        foreach ($configurables as $configurable) {
                            $sortedConfigurables[$configurable->getColor()] = $configurable;
                        }
                        ksort($sortedConfigurables);
                        foreach ($sortedConfigurables as $variant) {
                            if ($variant->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                                return $variant;
                            }
                        }

                    }
                    return $product;
                }
            } catch (NoSuchEntityException $e) {
                return $product;
            }
        }
        return $product;
    }

    public function getVar($name, $module = null)
    {
        $module = "Magento_Catalog";
        return parent::getVar($name, $module);
    }


}