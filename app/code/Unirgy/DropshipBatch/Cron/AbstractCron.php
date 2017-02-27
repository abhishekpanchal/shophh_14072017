<?php

namespace Unirgy\DropshipBatch\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;

class AbstractCron
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

    public function __construct(
        BatchFactory $modelBatchFactory,
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
}