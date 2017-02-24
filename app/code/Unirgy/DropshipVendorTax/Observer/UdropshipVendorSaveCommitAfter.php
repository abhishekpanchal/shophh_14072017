<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipVendorTax\Helper\Data as DropshipVendorTaxHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;

class UdropshipVendorSaveCommitAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $vendor = $observer->getVendor();
        $this->_udtaxHlp->processVendorChange($vendor);
    }
}
