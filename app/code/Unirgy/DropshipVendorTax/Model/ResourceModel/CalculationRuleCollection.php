<?php

namespace Unirgy\DropshipVendorTax\Model\ResourceModel;

class CalculationRuleCollection extends \Magento\Tax\Model\ResourceModel\Calculation\Rule\Collection
{
    protected function _afterLoadData()
    {
        parent::_afterLoadData();
        $this->addVendorTaxClassesToResult();
        return $this;
    }
    public function addVendorTaxClassesToResult()
    {
        return $this->_add('tax_class', 'vendor_tax_class_id', 'class_id', 'class_name', 'vendor_tax_classes');
    }
    public function setClassTypeFilter($type, $id)
    {
        switch ($type) {
            case \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT:
                $field = 'cd.product_tax_class_id';
                break;
            case \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER:
                $field = 'cd.customer_tax_class_id';
                break;
            case \Unirgy\DropshipVendorTax\Model\Source::TAX_CLASS_TYPE_VENDOR:
                $field = 'cd.vendor_tax_class_id';
                break;
            default:
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid type supplied'));
                break;
        }

        $this->joinCalculationData('cd');
        $this->addFieldToFilter($field, $id);
        return $this;
    }
}