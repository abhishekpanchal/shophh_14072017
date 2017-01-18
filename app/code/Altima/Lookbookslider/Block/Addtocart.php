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

class Addtocart extends \Magento\Framework\View\Element\Template {

    protected $_columnCountLayoutDepend = [];
    protected $_coreRegistry;
    protected $_taxData;
    protected $_catalogConfig;
    protected $_mathRandom;
    protected $_cartHelper;
    protected $stockRegistry;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, array $data = []) {
        $this->_cartHelper    = $context->getCartHelper();
        $this->_catalogConfig = $context->getCatalogConfig();
        $this->_coreRegistry  = $context->getRegistry();
        $this->_taxData       = $context->getTaxData();
        $this->_mathRandom    = $context->getMathRandom();
        $this->stockRegistry  = $context->getStockRegistry();
        parent::__construct($context, $data);
    }

    public function getAddToCartUrl($product, $additional = []) {
        if ($product->getTypeInstance()->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';
            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    public function getSubmitUrl($product, $additional = []) {
        $submitRouteData = $this->getData('submit_route_data');
        if ($submitRouteData) {
            $route     = $submitRouteData['route'];
            $params    = isset($submitRouteData['params']) ? $submitRouteData['params'] : [];
            $submitUrl = $this->getUrl($route, array_merge($params, $additional));
        } else {
            $submitUrl = $this->getAddToCartUrl($product, $additional);
        }
        return $submitUrl;
    }

    public function getMinimalQty($product) {
        $stockItem  = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minSaleQty = $stockItem->getMinSaleQty();
        return $minSaleQty > 0 ? $minSaleQty : null;
    }

    public function getProduct() {
        if (!$this->hasData('product')) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    protected function _addProductAttributesAndPrices(
    \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        return $collection
                        ->addMinimalPrice()
                        ->addFinalPrice()
                        ->addTaxPercents()
                        ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                        ->addUrlRewrite();
    }

    public function getProductUrl($product, $additional = []) {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    public function hasProductUrl($product) {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    public function displayProductStockStatus() {
        $statusInfo = new \Magento\Framework\DataObject(['display_status' => true]);
        $this->_eventManager->dispatch('catalog_block_product_status_display', ['status' => $statusInfo]);
        return (bool) $statusInfo->getDisplayStatus();
    }

    public function getRandomString($length, $chars = null) {
        return $this->_mathRandom->getRandomString($length, $chars);
    }

}
