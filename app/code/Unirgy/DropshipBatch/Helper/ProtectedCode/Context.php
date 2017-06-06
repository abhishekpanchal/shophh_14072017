<?php

namespace Unirgy\DropshipBatch\Helper\ProtectedCode;

use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\Dropship\Helper\ProtectedCode as HelperProtectedCode;
use Unirgy\Dropship\Model\VendorFactory;

class Context
{
    /**
     * @var ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var BatchFactory
     */
    public $_batchFactory;

    /**
     * @var ScheduleFactory
     */
    public $_scheduleFactory;

    /**
     * @var VendorFactory
     */
    public $_vendorFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configScopeConfigInterface,
        \Unirgy\DropshipBatch\Model\BatchFactory $modelBatchFactory,
        \Magento\Cron\Model\ScheduleFactory $modelScheduleFactory,
        \Unirgy\Dropship\Model\VendorFactory $modelVendorFactory)
    {
        $this->scopeConfig = $configScopeConfigInterface;
        $this->_batchFactory = $modelBatchFactory;
        $this->_scheduleFactory = $modelScheduleFactory;
        $this->_vendorFactory = $modelVendorFactory;

    }
}