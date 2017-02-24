<?php

namespace Unirgy\DropshipBatch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdpoPoSaveAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $this->_instantByStatusPoExport($observer->getPo());
    }
}
