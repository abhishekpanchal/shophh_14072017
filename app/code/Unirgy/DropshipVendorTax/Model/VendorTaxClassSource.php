<?php

namespace Unirgy\DropshipVendorTax\Model;

class VendorTaxClassSource implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $_hlp;

    public function __construct(\Unirgy\Dropship\Helper\Data $udropshipHelper)
    {
        $this->_hlp = $udropshipHelper;
    }

    public function toOptionArray()
    {
        return $this->_hlp->getObj('\Unirgy\DropshipVendorTax\Model\Source')->setPath('vendor_tax_class')->toOptionArray();
    }
    public function toOptionHash()
    {
        return $this->_hlp->getObj('\Unirgy\DropshipVendorTax\Model\Source')->setPath('vendor_tax_class')->toOptionHash();
    }
}