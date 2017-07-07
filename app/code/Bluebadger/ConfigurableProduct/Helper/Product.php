<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-06
 * Time: 15:35
 */

namespace Bluebadger\ConfigurableProduct\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Pricing\Price\ConfigurableOptionsProvider;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class Product extends AbstractHelper
{

    protected $productRepository;
    protected $configurableOptionsProvider;
    protected $attributeRepository;
    protected $attributeValues;
    protected $tableFactory;
    /** @var Configurable */
    private $configurable;

    public function __construct(
        Context $context,
        Configurable $configurable,
        ProductRepository $productRepository,
        ConfigurableOptionsProvider $configurableOptionsProvider,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory

    ) {
        $this->configurable = $configurable;
        $this->productRepository = $productRepository;
        $this->configurableOptionsProvider = $configurableOptionsProvider;
        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        parent::__construct($context);
    }


    public function getSelectedProduct(\Magento\Catalog\Model\Product $product)
    {

        $applyAttributes = ['color',"primary_color"];

        if ($product->getTypeId() == "configurable") {

            $configurables = $this->configurable->getUsedProducts($product);
            $params = $this->_getRequest()->getParams();

            foreach ($params as $attributeCode => $value) {
                if (in_array($attributeCode, $applyAttributes)) {
                    foreach ($configurables as $index => &$_variant) {
                        // Might need to explode value on _
                        if (!in_array($_variant->getData($attributeCode), [$value])) {
                            unset($configurables[ $index ]);
                            continue;
                        }
                    }
                }
            }

            if (count($configurables) && count($params)) {
                if (count($configurables) == 1) {
                    $product = array_shift($configurables);
                    $product = $this->productRepository->getById($product->getId());
                    return $product;
                }
            }

            $product = $this->productRepository->getById($product->getId());
            try {
                if (count($configurables)) {
                    $sortedConfigurables = [];
                    foreach ($configurables as $configurable) {
                        $sortedConfigurables[$configurable->getColor()] = $configurable;
                    }
                    ksort($sortedConfigurables);
                    foreach ($sortedConfigurables as $variant) {
                        if ($variant->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                            $variant = $this->productRepository->getById($variant->getId());
                            return $variant;
                        }
                    }
                }
                return $product;
            } catch (NoSuchEntityException $e) {
                return $product;
            }
        }
        return $product;
    }


    /**
     * @param $attributeCode
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface
     */
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }
}