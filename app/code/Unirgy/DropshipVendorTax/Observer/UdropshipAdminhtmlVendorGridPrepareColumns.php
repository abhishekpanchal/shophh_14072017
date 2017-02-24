<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipVendorTax\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;

class UdropshipAdminhtmlVendorGridPrepareColumns extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $grid = $observer->getGrid();
        $grid->addColumn('vendor_tax_class', [
            'header'        => __('Vendor Tax Class'),
            'index'         => 'vendor_tax_class',
            'type'          => 'options',
            'options'       => $this->_udtaxHlp->src()->setPath('vendor_tax_class')->toOptionHash(),
        ]);
        $grid->addColumnsOrder('action', 'vendor_tax_class');
    }
}
