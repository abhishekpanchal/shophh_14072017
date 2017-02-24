<?php

namespace Unirgy\VendorMinAmounts\Observer;

use Unirgy\VendorMinAmounts\Helper\Data as HelperData;

abstract class AbstractObserver
{
    /**
     * @var HelperData
     */
    protected $_minHlp;
    protected $_hlp;
    protected $_hlpPr;

    public function __construct(
        HelperData $helperData,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\Dropship\Helper\ProtectedCode $udropshipHelperProtected,
        \Magento\Tax\Helper\Data $taxHelper
    )
    {
        $this->_minHlp = $helperData;
        $this->_hlp    = $udropshipHelper;
        $this->_hlpPr  = $udropshipHelperProtected;
        $this->_taxHelper = $taxHelper;

    }

    public function getVendorMinOrderAmount($quote, $vendor, $subtotal)
    {
        return $this->_minHlp->getVendorMinOrderAmount($quote, $vendor, $subtotal);
    }

    public function addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal)
    {
        return $this->_minHlp->addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal);
    }


}
