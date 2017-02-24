<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Block\PaypalExpress;

use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Session as ModelSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Paypal\Block\Express\Review\Details;
use Magento\Quote\Model\Quote\Item;
use Magento\Sales\Model\Config;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipSplit\Helper\Data as HelperData;
use Unirgy\DropshipSplit\Model\Cart\VendorFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;
use Zend\Json\Json;

class ReviewDetails extends Details
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ProtectedCode
     */
    protected $_helperProtectedCode;

    /**
     * @var ProductFactory
     */
    protected $_modelProductFactory;

    /**
     * @var VendorFactory
     */
    protected $_cartVendorFactory;

    /**
     * @var DropshipHelperData
     */
    protected $_dropshipHelperData;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;

    public function __construct(Context $context, 
        Session $customerSession, 
        ModelSession $checkoutSession, 
        Config $salesConfig, 
        HelperData $helperData, 
        ProtectedCode $helperProtectedCode, 
        ProductFactory $modelProductFactory, 
        VendorFactory $cartVendorFactory,
        DropshipHelperData $dropshipHelperData, 
        LayoutFactory $viewLayoutFactory, 
        array $layoutProcessors = [], 
        array $data = [])
    {
        $this->_helperData = $helperData;
        $this->_helperProtectedCode = $helperProtectedCode;
        $this->_modelProductFactory = $modelProductFactory;
        $this->_cartVendorFactory = $cartVendorFactory;
        $this->_dropshipHelperData = $dropshipHelperData;
        $this->_viewLayoutFactory = $viewLayoutFactory;

        parent::__construct($context, $customerSession, $checkoutSession, $salesConfig, $layoutProcessors, $data);
    }

    public function getItems()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getItems();
        }

        $q = $this->getQuote();
        $a = $q->getShippingAddress();
        $methods = [];
        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = Json::decode($details);
            $methods = isset($details['methods']) ? $details['methods'] : [];
        }

        $quoteItems = $q->getAllVisibleItems();

        $this->_helperProtectedCode->prepareQuoteItems($a->getAllItems());

        $vendorItems = [];
        foreach ($quoteItems as $item) {
            $vendorItems[$item->getUdropshipVendor()][] = $item;
        }

        $rates = [];
        $qRates = $a->getGroupedAllShippingRates();
        foreach ($qRates as $cCode=>$cRates) {
            foreach ($cRates as $rate) {
                $vId = $rate->getUdropshipVendor();
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
            }
        }

        $items = [];
        $dummyProduct = $this->_modelProductFactory->create();
        foreach ($vendorItems as $vId=>$vItems) {
            if (!$this->_scopeConfig->isSetFlag('carriers/udsplit/hide_vendor_name', ScopeInterface::SCOPE_STORE)) {
                $items[] = $this->_cartVendorFactory->create()
                    ->setPart('header')
                    ->setQuote1($q)
                    ->setData('product', $dummyProduct)
                    ->setVendor($this->_dropshipHelperData->getVendor($vId));
            }
            foreach ($vItems as $item) {
                $items[] = $item;
            }

            $errorsOnly = false;
            if (!empty($rates[$vId])) {
                $errorsOnly = true;
                foreach ($rates[$vId] as $cCode=>$rs) {
//                    $hasRates = false;
                    foreach ($rs as $r) {
                        if (!$r->getErrorMessage()) {
//                            $hasRates = true;
                            $errorsOnly = false;
                        }
                    }
//                    if (!$hasRates) {
//                        unset($rates[$vId][$cCode]);
//                    }
                }
            }

            $items[] = $this->_cartVendorFactory->create()
                ->setPart('footer')
                ->setData('product', $dummyProduct)
                ->setVendor($this->_dropshipHelperData->getVendor($vId))
                ->setEstimateRates(isset($rates[$vId]) ? $rates[$vId] : [])
                ->setErrorsOnly($errorsOnly)
                ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                ->setItems($vItems)
                ->setQuote1($q);
        }

        return $items;
    }

    public function getItemHtml(Item $item)
    {
        if ($item instanceof \Unirgy\DropshipSplit\Model\Cart\Vendor) {
            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}";
            return $this->_viewLayoutFactory->create()->createBlock('udsplit/paypalExpress_vendor', $blockName)
                ->addData($item->getData())
                ->setShippingMethodSubmitUrl($this->getUrl("paypal/express/saveShippingMethod"))
                ->setQuote($item->getQuote1())
                ->toHtml();
        }

        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        return $renderer->toHtml();
    }
}