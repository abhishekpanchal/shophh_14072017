<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\Dropship\Helper\Data as HelperData;

class UdropshipQuoteItemSetUdropshipVendor extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $observer->getItem()->setVendorTaxClass(
            $this->_hlp->getVendor($observer->getItem()->getUdropshipVendor())->getVendorTaxClass()
        );
    }
}
