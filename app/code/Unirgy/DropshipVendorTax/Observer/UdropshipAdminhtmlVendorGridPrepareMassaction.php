<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipVendorTax\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;

class UdropshipAdminhtmlVendorGridPrepareMassaction extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $grid = $observer->getGrid();
        $grid->getMassactionBlock()->addItem('vendor_tax_class', [
            'label'=> __('Change Vendor Tax Class'),
            'url'  => $grid->getUrl('udtax/index/massUpdateVendorTaxClass'),
            'additional' => [
                'vendor_tax_class' => [
                    'name' => 'vendor_tax_class',
                    'type' => 'select',
                    'class' => 'required-entry',
                    'label' => __('Vendor Tax Class'),
                    'values' => $this->_udtaxHlp->src()->setPath('vendor_tax_class')->toOptionHash(true),
                ]
            ]
        ]);
    }
}
