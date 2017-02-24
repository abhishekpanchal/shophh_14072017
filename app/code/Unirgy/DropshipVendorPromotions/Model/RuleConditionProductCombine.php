<?php

namespace Unirgy\DropshipVendorPromotions\Model;

use Magento\Framework\DataObject;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine;

class RuleConditionProductCombine extends Combine
{
    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getUdropshipVendor()!=$this->getRule()->getUdropshipVendor() && $this->getRule()->getUdropshipVendor()) {
            return false;
        }
        if (!$this->getConditions()) {
            return true;
        }
        return parent::validate($object);
    }
}