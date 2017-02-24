<?php

namespace Unirgy\VendorMinAmounts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Tax\Helper\Data as TaxHelperData;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;
use Unirgy\VendorMinAmounts\Helper\Data as HelperData;

class SalesQuoteLoadAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $hl = $this->_hlp;
        $quote = $observer->getQuote();
        $qId = $quote->getId();
        if ($hl->isSkipQuoteLoadAfterEvent($qId)
            || $this->_minHlp->cartUpdateActionFlag
        ) {
            return;
        }

        $hlp = $this->_hlpPr;
        $items = $observer->getQuote()->getAllItems();
        $subtotalByVendor = [];
        foreach ($items as $item) {
            if (empty($subtotalByVendor[$item->getUdropshipVendor()])) {
                $subtotalByVendor[$item->getUdropshipVendor()] = 0;
            }
            if ($this->_taxHelper->priceIncludesTax()) {
                $subtotalByVendor[$item->getUdropshipVendor()] += $item->getBaseRowTotalInclTax();
            } else {
                $subtotalByVendor[$item->getUdropshipVendor()] += $item->getBaseRowTotal();
            }
            #$subtotalByVendor[$item->getUdropshipVendor()] -= $item->getBaseDiscountAmount();
        }
        foreach ($subtotalByVendor as $vId=>$subtotal) {
            $vendor = $this->_hlp->getVendor($vId);
            $minOrderAmount = null;
            if (!$vendor->getId()) continue;
            $minOrderAmount = $this->getVendorMinOrderAmount($observer->getQuote(), $vendor, $subtotal);
            if ($minOrderAmount !== false && $subtotal < $minOrderAmount) {
                $this->addVendorMinOrderAmountError($observer->getQuote(), $vendor, $minOrderAmount, $subtotal);
            }
        }
    }
}
