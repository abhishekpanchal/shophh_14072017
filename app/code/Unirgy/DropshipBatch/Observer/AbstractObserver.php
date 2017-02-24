<?php

namespace Unirgy\DropshipBatch\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;

abstract class AbstractObserver
{
    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var HelperData
     */
    protected $_bHlp;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(BatchFactory $modelBatchFactory, 
        HelperData $helperData, 
        DropshipHelperData $dropshipHelperData, 
        ScopeConfigInterface $configScopeConfigInterface, 
        LoggerInterface $logLoggerInterface)
    {
        $this->_batchFactory = $modelBatchFactory;
        $this->_bHlp = $helperData;
        $this->_hlp = $dropshipHelperData;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->_logger = $logLoggerInterface;

    }

    protected function _instantByStatusPoExport($po)
    {
        try {
            $v = $this->_hlp->getVendor($po->getUdropshipVendor());
            $exportOnPoStatus = $this->scopeConfig->getValue('udropship/batch/export_on_po_status', ScopeInterface::SCOPE_STORE);
            if (!is_array($exportOnPoStatus)) {
                $exportOnPoStatus = explode(',', $exportOnPoStatus);
            }
            if ($v->getId() && $v->getData("batch_export_orders_method") == 'status_instant'
                && in_array($po->getUdropshipStatus(), $exportOnPoStatus)
            ) {
                $batch = $this->_bHlp->createBatch('export_orders', $v, 'processing')->save();
                $batch->addPOToExport($po)->exportOrders();
            }
        } catch (\Exception $e) {
            $this->_logger->error($e);
        }
    }

    protected function _instantPoExport($pos)
    {
        foreach ($pos as $po) {
            $v = $this->_hlp->getVendor($po->getUdropshipVendor());
            if ($v->getId() && $v->getData("batch_export_orders_method") == 'instant') {
                $batch = $this->_bHlp->createBatch('export_orders', $v, 'processing')->save();
                $batch->addPOToExport($po)->exportOrders();
            }
        }
    }

}