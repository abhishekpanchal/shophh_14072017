<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipBatch
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Model\ResourceModel\Batch;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipBatch\Helper\Data as DropshipBatchHelperData;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\PoFactory;

class Collection extends AbstractCollection
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipBatchHelperData
     */
    protected $_batchHelper;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var BatchFactory
     */
    protected $_modelBatchFactory;

    public function __construct(EntityFactoryInterface $entityFactory, 
        LoggerInterface $logger, 
        FetchStrategyInterface $fetchStrategy, 
        ManagerInterface $eventManager, 
        HelperData $helperData, 
        DropshipBatchHelperData $batchHelper,
        PoFactory $modelPoFactory, 
        BatchFactory $modelBatchFactory,
        AdapterInterface $connection = null, 
        AbstractDb $resource = null)
    {
        $this->_hlp = $helperData;
        $this->_batchHelper = $batchHelper;
        $this->_poFactory = $modelPoFactory;
        $this->_modelBatchFactory = $modelBatchFactory;

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected $_batches = [];

    protected function _construct()
    {
        $this->_init('Unirgy\DropshipBatch\Model\Batch', 'Unirgy\DropshipBatch\Model\ResourceModel\Batch');
        parent::_construct();
    }

    public function resetBatches()
    {
        $this->_batches = [];
    }

    public function loadScheduledBatches()
    {
        $hlp = $this->_hlp;
        $hlpb = $this->_batchHelper;

        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        // find all scheduled batches scheduled for earlier than now, sorted by schedule time
        $this->addFieldToFilter('batch_status', 'scheduled')
            ->addFieldToFilter('scheduled_at', ['datetime'=>true, 'to'=>$now]);
        $this->getSelect()->order('scheduled_at');

        // preprocess batches and set correct statuses
        foreach ($this->getItems() as $b) {
            $this->addBatch($b, true);
        }

        foreach($this->getItems() as $b) {
            if ($b->getBatchStatus()!='processing') {
                $this->removeBatch($b);
            }
        }

        return $this;
    }

    public function addBatch($batch, $validate=false)
    {
        $batch->setBatchStatus('processing')->save();
        $type = $batch->getBatchType();
        $vId = $batch->getVendorId();
        if ($validate) {
            // if vendors are not configured to be scheduled anymore, mark as canceled
            if (!$this->_batchHelper->isVendorEnabled($this->_hlp->getVendor($vId), $type, true)) {
                $batch->setBatchStatus('canceled')->save();
                return $this;
            }
            // if multiple batches for the same vendor/type exist, mark older batches as missed
            elseif (!empty($this->_batches[$type][$vId])) {
                #$this->_batches[$type][$vId]->setBatchStatus('missed')->save();
                $this->_batches[$type][$vId]->delete();
            }
        }
        $this->_batches[$type][$vId] = $batch;
        return $this;
    }

    public function removeBatch($batch)
    {
        $this->removeItemByKey($batch->getId());
        return $this;
    }

    public function getBatchesByType($type)
    {
        return !empty($this->_batches[$type]) ? $this->_batches[$type] : [];
    }

    public function addPendingPOsToExport($vendorIds=null)
    {
        if ($vendorIds===true) {
            if (empty($this->_batches['export_orders'])) {
                return $this;
            }
            $vendorIds = array_keys($this->_batches['export_orders']);
        }
        $pos = $this->_poFactory->create()->getCollection()
            ->addPendingBatchStatusVendorFilter($vendorIds);
        if (!is_null($vendorIds)) {
            $pos->addAttributeToFilter('udropship_vendor', ['in'=>(array)$vendorIds]);
        }

        foreach ($pos as $po) {
            $this->addPOToExport($po);
        }

        return $this;
    }

    public function addPendingStockPOsToExport($vendorIds=null)
    {
        if ($vendorIds===true) {
            if (empty($this->_batches['export_stockpo'])) {
                return $this;
            }
            $vendorIds = array_keys($this->_batches['export_stockpo']);
        }

        $stockPoIds = $this->_hlp->createObj('Unirgy\DropshipStockPo\Model\Po')->getCollection()
            ->addAttributeToFilter('ustock_vendor', ['in'=>(array)$vendorIds])
            ->addPendingBatchStatusFilter()
            ->getAllIds();

        $pos = [];

        if (!empty($stockPoIds)) {
            $pos = $this->_hlp->createObj('Unirgy\DropshipPo\Model\Po')->getCollection()
                ->addAttributeToFilter('ustockpo_id', ['in'=>$stockPoIds])
                ->addPendingStockpoBatchStatusFilter()
                ->addOrders()
                ->addStockPos();
        }
        foreach ($pos as $po) {
            $this->addStockPOToExport($po);
        }

        return $this;
    }

    public function addPOToExport($po)
    {
        $vId = $po->getUdropshipVendor();
        if (empty($this->_batches['export_orders'][$vId])) {
            $batch = false;
            foreach ($this->getItems() as $item) {
                if ($item->getVendorId()==$vId
                    && $item->getBatchType()=='export_orders'
                    && $item->getBatchStatus()=='processing'
                ) {
                    $batch = $item;
                    break;
                }
            }
            if (!$batch) {
                $batch = $this->_modelBatchFactory->create()->setVendorId($vId);
                $this->addItem($batch);
            }
            $this->_batches['export_orders'][$vId] = $batch;
        } else {
            $batch = $this->_batches['export_orders'][$vId];
        }
        $batch->addPOToExport($po);
        return $this;
    }

    public function addStockPOToExport($po)
    {
        $vId = $po->getUstockVendor();
        if (empty($this->_batches['export_stockpo'][$vId])) {
            $batch = false;
            foreach ($this->getItems() as $item) {
                if ($item->getVendorId()==$vId
                    && $item->getBatchType()=='export_stockpo'
                    && $item->getBatchStatus()=='processing'
                ) {
                    $batch = $item;
                    break;
                }
            }
            if (!$batch) {
                $batch = $this->_modelBatchFactory->create()->setVendorId($vId);
                $this->addItem($batch);
            }
            $this->_batches['export_stockpo'][$vId] = $batch;
        } else {
            $batch = $this->_batches['export_stockpo'][$vId];
        }
        $batch->addStockPOToExport($po);
        return $this;
    }

    public function exportOrders()
    {
        if (empty($this->_batches['export_orders'])) {
            return $this;
        }
        foreach ($this->_batches['export_orders'] as $batch) {
            $batch->exportOrders();
        }
        return $this;
    }
    
    public function exportStockpo()
    {
        if (empty($this->_batches['export_stockpo'])) {
            return $this;
        }
        foreach ($this->_batches['export_stockpo'] as $batch) {
            $batch->exportStockpo();
        }
        return $this;
    }

    public function importOrders()
    {
        if (empty($this->_batches['import_orders'])) {
            return $this;
        }
        foreach ($this->_batches['import_orders'] as $batch) {
            $batch->importOrders();
        }
        return $this;
    }
    
    public function importStockpo()
    {
        if (empty($this->_batches['import_stockpo'])) {
            return $this;
        }
        foreach ($this->_batches['import_stockpo'] as $batch) {
            $batch->importStockpo();
        }
        return $this;
    }
    
	public function importInventory()
    {
        if (empty($this->_batches['import_inventory'])) {
            return $this;
        }
        foreach ($this->_batches['import_inventory'] as $batch) {
            $batch->importInventory();
        }
        return $this;
    }

}