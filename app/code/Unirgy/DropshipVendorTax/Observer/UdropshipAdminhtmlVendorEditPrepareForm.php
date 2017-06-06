<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipVendorTax\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;

class UdropshipAdminhtmlVendorEditPrepareForm extends AbstractObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $form = $observer->getForm();
        $vForm = $form->getElement('vendor_form');
        if ($vForm) {
            $hlp = $this->_hlp;
            $vForm->addField('vendor_tax_class', 'select', [
                'name'      => 'vendor_tax_class',
                'label'     => __('Vendor Tax Class'),
                'values'    => $this->_udtaxHlp->src()->setPath('vendor_tax_class')->toOptionArray(),
            ]);
        }
    }
}
