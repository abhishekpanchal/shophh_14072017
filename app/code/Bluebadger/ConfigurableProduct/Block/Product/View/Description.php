<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-06
 * Time: 16:18
 */

namespace Bluebadger\ConfigurableProduct\Block\Product\View;

class Description extends \Magento\Catalog\Block\Product\View\Description {

    protected $_productHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Bluebadger\ConfigurableProduct\Helper\Product $productHelper,
        array $data = []
    ) {
        $this->_productHelper = $productHelper;
        parent::__construct($context, $registry, $data);
    }


    /**
     * @inheritdoc
     */
    public function getProduct()
    {
        $product = parent::getProduct();

        if ($product->getTypeId() == "configurable") {
            return $this->_productHelper->getSelectedProduct($product);
        }
        return $product;
    }


}