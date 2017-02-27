<?php

namespace Unirgy\DropshipBatch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdropshipOrderSaveAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $pos = $observer->getShipments();
        if ($pos) {
            try {
                $this->_instantPoExport($pos);
            } catch (\Exception $e) {
                $this->_logger->error($e);
            }
        }
    }
}
