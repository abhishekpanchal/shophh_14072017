<?php

namespace Unirgy\DropshipVendorImport\Helper\ProtectedCode;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Unirgy\DropshipVendorImport\Helper\Data as DropshipVendorImportHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\ProtectedCode as HelperProtectedCode;

class Context
{
    /**
     * @var HelperData
     */
    public $_hlp;

    /**
     * @var DropshipVendorImportHelperData
     */
    public $_viHlp;

    /**
     * @var ScopeConfigInterface
     */
    public $_scopeConfig;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $helperData,
        \Unirgy\DropshipVendorImport\Helper\Data $dropshipVendorImportHelperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $dateTimeTimezoneInterface
    )
    {
        $this->_hlp = $helperData;
        $this->_viHlp = $dropshipVendorImportHelperData;
        $this->_scopeConfig = $scopeConfig;

    }
}