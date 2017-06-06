<?php

namespace Unirgy\DropshipVendorPromotions\Model;

use Magento\Directory\Model\Config\Source\Allregion;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\DataObject;
use Magento\Payment\Model\Config\Source\Allmethods as SourceAllmethods;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Address;
use Magento\Shipping\Model\Config\Source\Allmethods;
use Unirgy\DropshipVendorPromotions\Helper\Data as HelperData;

class RuleConditionAddress extends Address
{
    /**
     * @var HelperData
     */
    protected $_promoHlp;

    public function __construct(Context $context, 
        Country $directoryCountry, 
        Allregion $directoryAllregion, 
        Allmethods $shippingAllmethods, 
        SourceAllmethods $paymentAllmethods, 
        HelperData $helperData, 
        array $data = [])
    {
        $this->_promoHlp = $helperData;

        parent::__construct($context, $directoryCountry, $directoryAllregion, $shippingAllmethods, $paymentAllmethods, $data);
    }

    public function validate(\Magento\Framework\Model\AbstractModel $object)
    {
        $address = $object;
        $attr = $this->getAttribute();
        $ruleVid = $this->getRule()->getUdropshipVendor();
        if (in_array($attr, ['base_subtotal', 'weight', 'total_qty']) && $ruleVid) {
            $origTotal = $address->getData($attr);
            $vendorTotal = $this->_promoHlp->getQuoteAddrTotal($address, $attr, $ruleVid);
            $address->setData($attr, $vendorTotal);
        }

        $valResult = parent::validate($address);

        if (in_array($attr, ['base_subtotal', 'weight', 'total_qty']) && $ruleVid && isset($origTotal)) {
            $address->setData($attr, $origTotal);
        }

        return $valResult;
    }
}