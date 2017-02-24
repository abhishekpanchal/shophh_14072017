<?php

namespace Unirgy\DropshipVendorTax\Model;

class CalculationRule extends \Magento\Tax\Model\Calculation\Rule
{
    const KEY_VENDOR_TAX_CLASS_IDS = 'vendor_tax_class_ids';
    public function saveCalculationData()
    {
        $ctc = $this->getData('customer_tax_class_ids');
        $ptc = $this->getData('product_tax_class_ids');
        $vtc = $this->getData('vendor_tax_class_ids');
        $rates = $this->getData('tax_rate_ids');

        $this->_calculation->deleteByRuleId($this->getId());
        foreach ($ctc as $c) {
            foreach ($ptc as $p) {
                foreach ($vtc as $v) {
                    foreach ($rates as $r) {
                        $dataArray = [
                            'tax_calculation_rule_id' => $this->getId(),
                            'tax_calculation_rate_id' => $r,
                            'customer_tax_class_id' => $c,
                            'product_tax_class_id' => $p,
                            'vendor_tax_class_id' => $v,
                        ];
                        $this->_calculation->setData($dataArray)->save();
                    }
                }
            }
        }
    }
    public function getVendorTaxClassIds()
    {
        $ids = $this->getData(self::KEY_VENDOR_TAX_CLASS_IDS);
        if (null === $ids) {
            $ids = $this->_getUniqueValues($this->getVendorTaxClasses());
            $this->setData(self::KEY_VENDOR_TAX_CLASS_IDS, $ids);
        }
        return $ids;
    }
    public function getVendorTaxClasses()
    {
        return $this->getCalculationModel()->getVendorTaxClasses($this->getId());
    }
    public function setVendorTaxClassIds(array $vendorTaxClassIds = null)
    {
        return $this->setData(self::KEY_VENDOR_TAX_CLASS_IDS, $vendorTaxClassIds);
    }
}