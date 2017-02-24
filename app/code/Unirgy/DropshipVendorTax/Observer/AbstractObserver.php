<?php

namespace Unirgy\DropshipVendorTax\Observer;

use Unirgy\Dropship\Helper\Data as HelperData;

abstract class AbstractObserver
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    public function __construct(
        HelperData $udropshipHelper,
        \Unirgy\DropshipVendorTax\Helper\Data $udtaxHelper
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_udtaxHlp = $udtaxHelper;
    }



    protected function _initConfigRewrites()
    {
        return;
        Mage::getConfig()->setNode('global/helpers/tax/rewrite/data', 'Unirgy\DropshipVendorTax\Helper\Tax');
        if (
        $this->_helperData->compareMageVer('1.7.0.0', '1.12.0.0')
        ) {
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1700\Tax\ClassTax');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class_edit_form', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1700\Tax\ClassTax\Edit\Form');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_rule_edit_form', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1700\Tax\Rule\Edit\Form');

            if (
            $this->_helperData->compareMageVer('1.8.1.0', '1.13.0.0')
            ) {
                Mage::getConfig()->setNode('global/helpers/tax/rewrite/data', 'Unirgy\DropshipVendorTax\Helper\Tax19');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Calculation');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Calculation\Rule');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation_rule', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Resource\Calculation\Rule');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Sales\Total\Quote\Subtotal');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Sales\Total\Quote\Tax');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_shipping', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Sales\Total\Quote\Shipping');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1900\Tax\Resource\Calculation');
            } else {
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Calculation');
                Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Calculation\Rule');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Sales\Total\Quote\Subtotal');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Sales\Total\Quote\Tax');
                Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_shipping', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Sales\Total\Quote\Shipping');
                Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Resource\Calculation');
            }

            Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation_rule_collection', 'Unirgy\DropshipVendorTax\Model\Rewrite1700\Tax\Resource\Calculation\Rule\Collection');
        }
        elseif (
        $this->_helperData->compareMageVer('1.6.0.0', '1.11.0.0')
        ) {
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1600\Tax\ClassTax');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_class_edit_form', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1600\Tax\ClassTax\Edit\Form');
            Mage::getConfig()->setNode('global/blocks/adminhtml/rewrite/tax_rule_edit_form', 'Unirgy\DropshipVendorTax\Block\Adminhtml\Rewrite1600\Tax\Rule\Edit\Form');

            Mage::getConfig()->setNode('global/models/tax/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1600\Tax\Calculation');
            Mage::getConfig()->setNode('global/models/tax/rewrite/calculation_rule', 'Unirgy\DropshipVendorTax\Model\Rewrite1600\Tax\Calculation\Rule');
            Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_subtotal', 'Unirgy\DropshipVendorTax\Model\Rewrite1600\Tax\Sales\Total\Quote\Subtotal');
            Mage::getConfig()->setNode('global/models/tax/rewrite/sales_total_quote_tax', 'Unirgy\DropshipVendorTax\Model\Rewrite1600\Tax\Sales\Total\Quote\Tax');

            Mage::getConfig()->setNode('global/models/tax_resource/rewrite/calculation', 'Unirgy\DropshipVendorTax\Model\Rewrite1600\Tax\Resource\Calculation');
        }
        if (!$this->_helperData->isUdsplitActive()) {
            Mage::getConfig()->setNode('global/blocks/checkout/rewrite/cart_shipping', 'Unirgy\DropshipVendorTax\Block\CartShipping');
            Mage::getConfig()->setNode('global/blocks/checkout/rewrite/onepage_shipping_method_available', 'Unirgy\DropshipVendorTax\Block\OnepageShipping');
        }
    }

}