<?php

namespace Bluebadger\Catalog\Plugin;

use Magento\Catalog\Model\Product\Type;

/**
 * Class ProductUrl
 * @package Bluebadger\Catalog\Plugin
 */
class ProductUrl
{
    const KEY_COLOR = 'color';
    const KEY_QUERY = '_query';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductUrl constructor.
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->productRepository = $productRepository;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Catalog\Model\Product\Url $url
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return \Magento\Catalog\Model\Product\Url|mixed
     */
    public function aroundGetUrl(
        \Magento\Catalog\Model\Product\Url $url,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product,
        $params = []
    )
    {
        if ($product->getTypeId() === Type::TYPE_SIMPLE) {
            $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());

            if (isset($parentByChild[0])) {
                $color = $product->getResource()->getAttributeRawValue(
                    $product->getId(),
                    self::KEY_COLOR,
                    $this->storeManager->getStore()->getId()
                );

                if ($color) {
                    $params[self::KEY_QUERY] = [self::KEY_COLOR => $color];
                }

                $product = $this->productRepository->getById($parentByChild[0]);
            }
        }

        $url = $proceed($product, $params);

        return $url;
    }
}