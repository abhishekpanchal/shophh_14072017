<?php

namespace Unirgy\DropshipVendorTax\Controller\Adminhtml\Rule;

class Save extends \Magento\Tax\Controller\Adminhtml\Rule\Save
{
    protected function populateTaxRule($postData)
    {
        $taxRule = $this->taxRuleDataObjectFactory->create();
        if (isset($postData['tax_calculation_rule_id'])) {
            $taxRule->setId($postData['tax_calculation_rule_id']);
        }
        if (isset($postData['code'])) {
            $taxRule->setCode($postData['code']);
        }
        if (isset($postData['tax_rate'])) {
            $taxRule->setTaxRateIds($postData['tax_rate']);
        }
        if (isset($postData['tax_customer_class'])) {
            $taxRule->setCustomerTaxClassIds($postData['tax_customer_class']);
        }
        if (isset($postData['tax_product_class'])) {
            $taxRule->setProductTaxClassIds($postData['tax_product_class']);
        }
        if (isset($postData['priority'])) {
            $taxRule->setPriority($postData['priority']);
        }
        if (isset($postData['calculate_subtotal'])) {
            $taxRule->setCalculateSubtotal($postData['calculate_subtotal']);
        }
        if (isset($postData['position'])) {
            $taxRule->setPosition($postData['position']);
        }
        if (isset($postData['tax_vendor_class'])) {
            $taxRule->setVendorTaxClassIds($postData['tax_vendor_class']);
        }
        return $taxRule;
    }
}