<?php

namespace Unirgy\DropshipVendorTax\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Vendor;

class Data extends AbstractHelper
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    public function __construct(
        Context $context,
        HelperData $helperData
    )
    {
        $this->_hlp = $helperData;

        parent::__construct($context);
    }

    public function processVendorChange($vendor)
    {
    }
    public function setVendorClassId(\Magento\Tax\Api\Data\QuoteDetailsItemInterface $itemDataObject, $item)
    {
        if ($item instanceof Vendor || is_scalar($item)) {
            $v = $this->_hlp->getVendor($item);
        } else {
            $v = $this->_hlp->getVendor($item->getUdropshipVendor());
        }
        if ($v->getId()) {
            $itemDataObject->setVendorClassId($v->getVendorTaxClass());
            $itemDataObject->setUdropshipVendor($v->getId());
        }
    }
    public function setRequestVendorClassId($request, $item)
    {
        if ($item instanceof Vendor || is_scalar($item)) {
            $v = $this->_hlp->getVendor($item);
        } else {
            $v = $this->_hlp->getVendor($item->getUdropshipVendor());
        }
        if ($v->getId()) {
            $request->setVendorClassId($v->getVendorTaxClass());
            $basedOn = $this->scopeConfig->getValue(Config::CONFIG_XML_PATH_BASED_ON, ScopeInterface::SCOPE_STORE, $request->getStore());
            if ($basedOn=='origin') {
                $request
                    ->setCountryId($v->getCountryId())
                    ->setRegionId($v->getRegionId())
                    ->setPostcode($v->getZip());
            }
        }
    }

    /**
     * @return \Unirgy\DropshipVendorTax\Model\Source
     */
    public function src()
    {
        return $this->_hlp->getObj('\Unirgy\DropshipVendorTax\Model\Source');
    }
}
