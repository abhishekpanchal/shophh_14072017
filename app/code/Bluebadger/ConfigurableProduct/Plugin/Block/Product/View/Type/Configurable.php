<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-06
 * Time: 19:04
 */

namespace Bluebadger\ConfigurableProduct\Plugin\Block\Product\View\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Configurable
{

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;


    protected $_helper;

    protected $_helper_online;
    /**
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @param \Bluebadger\ConfigurableProduct\Helper\Data $helper
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        DecoderInterface $decoder,
        EncoderInterface $encoder,
        PageFactory $pageFactory,
        StockStateInterface $stockState,
        Context $context,
        PriceCurrencyInterface $priceCurrency,
        \Bluebadger\ConfigurableProduct\Helper\Data $helper,
        \Bluebadger\ConfigurableProduct\Helper\Product $helperOnline,
        \Magento\Framework\Registry $registry,
        ImageBuilder $imageBuilder
    ) {
        $this->imageBuilder  = $imageBuilder;
        $this->stockState = $stockState;
        $this->productRepository = $productRepository;
        $this->decoder = $decoder;
        $this->encoder = $encoder;
        $this->resultPageFactory = $pageFactory;
        $this->context = $context;
        $this->_helper = $helper;
        $this->priceCurrency = $priceCurrency;
        $this->_helper_online = $helperOnline;
        $this->_coreRegistry = $registry;
    }


    /**
     * Replace ',' on '.' for js
     *
     * @param float $price
     * @return string
     */
    protected function _registerJsPrice($price)
    {
        return str_replace(',', '.', $price);
    }


    public function afterGetJsonConfig(
        $subject,
        $result
    ) {

        $resultPage = $this->resultPageFactory->create();
        $config = $this->decoder->decode($result);
        foreach ($config['optionPrices'] as $pid => &$prices) {
            $priceRender = $resultPage->getLayout()->getBlock('product.price.render.default');
            $price = '';

            $product = $this->productRepository->getById($pid);

            if ($priceRender) {
                $price = $priceRender->render('final_price', $product, []);
            }

            $prices['price_box'] = $price;
        }

        $productData = [];
        $originalProduct = $this->_coreRegistry->registry("product");
        foreach ($config['index'] as $pid => $indexData) {
            $productData[ $pid ] = [];

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($pid); // This method caches the product calls from earlier, so no efficiency loss


            $localData = [
                'sku'                => $product->getSku(),
                'name'               => $product->getName(),
                'short_description'  => $product->getData('short_description') ?: "",
                'description'        => $product->getDescription(),

            ];
            $attributes = $product->getAttributes();

            foreach ($attributes as $attribute) {
                if ($attribute->getIsVisibleOnFront()) {
                    $value = $attribute->getFrontend()->getValue($product);
                    if (is_numeric($value)) {
                        $value = round($value, 2); //round to 0.00
                    }

                    if (!$product->hasData($attribute->getAttributeCode())) {
                        $value = __('N/A');
                    } elseif ((string)$value == '') {
                        $value = __('No');
                    } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                        $value = $this->priceCurrency->convertAndFormat($value);
                    }
                    if (is_string($value) && strlen($value)) {
                        $localData[$attribute->getAttributeCode()] = $value;
                    }
                }
            }

            $productData[ $pid ] = $localData;
        }
        $this->_coreRegistry->unregister("product");
        $this->_coreRegistry->register("product", $originalProduct );
        $config['product_data'] = $productData;
        return $this->encoder->encode($config);
    }


    /**
     * Filters out disabled products
     * @param \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject
     * @param $result
     * @return array
     */
    public function afterGetAllowProducts(
        \Magento\ConfigurableProduct\Block\Product\View\Type\Configurable $subject,
        $result
    ) {
        $enabledUsedProducts = [];
        foreach ($result as $_product) {
            if ($_product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            ) {
                $enabledUsedProducts[] = $_product;
            }
        }
        return $enabledUsedProducts;
    }
}
