<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdropshipVendorFrontPreferences extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $data = $observer->getEvent()->getData();
    }
}
