<?php

namespace Unirgy\DropshipBatch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderShipmentSaveAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        if (!$this->_hlp->isUdpoActive()) $this->_instantByStatusPoExport($observer->getShipment());
    }
}
