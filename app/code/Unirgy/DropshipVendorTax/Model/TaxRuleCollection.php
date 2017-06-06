<?php

namespace Unirgy\DropshipVendorTax\Model;

use Magento\Tax\Api\Data\TaxRuleInterface;

class TaxRuleCollection extends \Magento\Tax\Model\TaxRuleCollection
{
    protected function createTaxRuleCollectionItem(TaxRuleInterface $taxRule)
    {
        $collectionItem = new \Magento\Framework\DataObject();
        $collectionItem->setTaxCalculationRuleId($taxRule->getId());
        $collectionItem->setCode($taxRule->getCode());
        /* should cast to string so that some optional fields won't be null on the collection grid pages */
        $collectionItem->setPriority((string)$taxRule->getPriority());
        $collectionItem->setPosition((string)$taxRule->getPosition());
        $collectionItem->setCalculateSubtotal($taxRule->getCalculateSubtotal() ? '1' : '0');
        $collectionItem->setCustomerTaxClasses($taxRule->getCustomerTaxClassIds());
        $collectionItem->setProductTaxClasses($taxRule->getProductTaxClassIds());
        $collectionItem->setVendorTaxClasses($taxRule->getVendorTaxClassIds());
        $collectionItem->setTaxRates($taxRule->getTaxRateIds());
        return $collectionItem;
    }
}