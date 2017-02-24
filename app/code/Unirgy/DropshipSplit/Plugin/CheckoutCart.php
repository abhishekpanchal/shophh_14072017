<?php

namespace Unirgy\DropshipSplit\Plugin;

use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Url;
use Magento\Checkout\Block\Cart as BlockCart;
use Magento\Checkout\Helper\Cart as HelperCart;
use Magento\Checkout\Model\Session as ModelSession;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipSplit\Helper\Data as HelperData;
use Unirgy\DropshipSplit\Model\Cart\Vendor;
use Unirgy\DropshipSplit\Model\Cart\VendorFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;
use Zend\Json\Json;

class CheckoutCart
{
    /**
     * @var HelperData
     */
    protected $_splitHlp;

    /**
     * @var ProtectedCode
     */
    protected $_hlpPr;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var VendorFactory
     */
    protected $_cartVendorFactory;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    public function __construct(
        HelperData $helperData,
        ProtectedCode $helperProtectedCode,
        ProductFactory $modelProductFactory,
        VendorFactory $cartVendorFactory,
        DropshipHelperData $dropshipHelperData
    )
    {
        $this->_splitHlp = $helperData;
        $this->_hlpPr = $helperProtectedCode;
        $this->_productFactory = $modelProductFactory;
        $this->_cartVendorFactory = $cartVendorFactory;
        $this->_hlp = $dropshipHelperData;
    }
    public function aroundGetItems(\Magento\Checkout\Block\Cart $subject, \Closure $next)
    {
        if (!$this->_splitHlp->isActive()) {
            return $next();
        }

        $q = $subject->getQuote();
        $a = $q->getShippingAddress();
        $methods = [];
        $details = $a->getUdropshipShippingDetails();
        if ($details) {
            $details = $this->_hlp->unserializeArr($details);
            $methods = isset($details['methods']) ? $details['methods'] : [];
        }

        $quoteItems = $q->getAllVisibleItems();

        $this->_hlpPr->prepareQuoteItems($a->getAllItems());

        $vendorItems = [];
        foreach ($quoteItems as $item) {
            $vendorItems[$item->getUdropshipVendor()][] = $item;
        }

        $udsErr = null;
        $rates = [];
        $qRates = $a->getGroupedAllShippingRates();
        foreach ($qRates as $cCode=>$cRates) {
            foreach ($cRates as $rate) {
                $vId = $rate->getUdropshipVendor();
                if ($rate->getCode()=='udsplit_error') {
                    $udsErr = $rate;
                }
                if (!$vId) {
                    continue;
                }
                $rates[$vId][$cCode][] = $rate;
            }
        }

        $items = [];
        $dummyProduct = $this->_productFactory->create();
        foreach ($vendorItems as $vId=>$vItems) {
            if (!$this->_hlp->getScopeFlag('carriers/udsplit/hide_vendor_name')) {
                $items[] = $this->_cartVendorFactory->create()
                    ->setPart('header')
                    ->setQuote1($q)
                    ->setData('product', $dummyProduct)
                    ->setVendor($this->_hlp->getVendor($vId));
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
            } elseif ($udsErr) {
                $errorsOnly = true;
            }

            $items[] = $this->_cartVendorFactory->create()
                ->setPart('footer')
                ->setData('product', $dummyProduct)
                ->setVendor($this->_hlp->getVendor($vId))
                ->setEstimateRates(isset($rates[$vId]) ? $rates[$vId] : [])
                ->setErrorsOnly($errorsOnly)
                ->setShippingMethod(isset($methods[$vId]) ? $methods[$vId] : null)
                ->setItems($vItems)
                ->setQuote1($q);
        }

        return $items;
    }
    public function aroundGetItemHtml(\Magento\Checkout\Block\Cart $subject, \Closure $next, Item $item)
    {
        if ($item instanceof Vendor) {
            $blockName = "vendor_{$item->getVendor()->getId()}_{$item->getPart()}";
            return $subject->getLayout()->createBlock('\Unirgy\DropshipSplit\Block\Cart\Vendor', $blockName)
                ->addData($item->getData())
                ->setQuote($item->getQuote1())
                ->toHtml();
        }

        return $next($item);
    }
}
