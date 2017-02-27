<?php

namespace Unirgy\VendorMinAmounts\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPredispatchCheckoutCartAdd extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $this->_minHlp->cartUpdateActionFlag = true;
    }
}
