<?php

namespace Unirgy\DropshipBatch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UdropshipAdminhtmlVendorTabsAfter extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $block = $observer->getEvent()->getBlock();
        $id = $observer->getEvent()->getId();
        $v = $this->_hlp->getVendor($id);

        if ($this->_bHlp->isVendorEnabled($v)) {
            $block->addTab('batches_section', [
                'label'     => __('Import/Export Batches'),
                'title'     => __('Import/Export Batches'),
                'content'   => $block->getLayout()->createBlock('\Unirgy\DropshipBatch\Block\Adminhtml\Vendor\Batch\Grid', 'udropship.batch.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ]);
        }
    }
}
