<?php

namespace Unirgy\VendorMinAmounts\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Helper\Data as TaxHelperData;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\VendorMinAmounts\Helper\Data as HelperData;

class UdropshipProcessVendorCarrierSingleRateResult extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $request  = $observer->getRequest();
        $rate     = $observer->getRate();
        $udMethod = $observer->getUdmethod();
        $_udMethod = $udMethod instanceof DataObject ? $udMethod->getShippingCode() : $udMethod;

        $vendorSubtotal = 0;
        foreach ($request->getAllItems() as $item) {
            if ($this->_taxHelper->priceIncludesTax()) {
                $vendorSubtotal += $item->getBaseRowTotalInclTax()-$item->getBaseDiscountAmount();
            } else {
                $vendorSubtotal += $item->getBaseRowTotal()-$item->getBaseDiscountAmount();
            }
        }

        $freeMethods = explode(',', $this->_hlp->getScopeConfig('carriers/udropship/free_method', $request->getStoreId()));
        $vendor = $this->_hlp->getVendor($request->getVendorId());
        $freeShippingSubtotal = null;
        if ($vendor->getId()) {
            $freeShippingSubtotal = $vendor->getFreeShippingSubtotal();
        }
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = $this->_hlp->getScopeConfig('carriers/udropship/vendor_free_shipping_subtotal', $request->getStoreId());
        }
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = false;
        }
        if (in_array($_udMethod, $freeMethods)
            && $this->_hlp->getScopeFlag('carriers/udropship/free_shipping_allowed', $request->getStoreId())
            && $this->_hlp->getScopeFlag('carriers/udropship/free_shipping_enable', $request->getStoreId())
            && $freeShippingSubtotal!==false
            && $freeShippingSubtotal <= $vendorSubtotal
        ) {
            $rate->setPrice('0.00');
        }
        return $this;
    }
}
