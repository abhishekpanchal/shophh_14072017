<?php

namespace Unirgy\DropshipBatch\Helper;

use Magento\Cron\Model\Schedule;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\DropshipBatch\Model\Batch\DistFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Vendor;

class Data extends AbstractHelper
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var DirectoryList
     */
    protected $_dirList;

    /**
     * @var DistFactory
     */
    protected $_batchDistFactory;

    /**
     * @var ProtectedCode
     */
    protected $_bHlpPr;

    public function __construct(Context $context,
        HelperData $helperData, 
        BatchFactory $modelBatchFactory, 
        DirectoryList $filesystemDirectoryList,
        DistFactory $batchDistFactory,
        ProtectedCode $helperProtectedCode, 
        CacheInterface $appCacheInterface)
    {
        $this->_hlp = $helperData;
        $this->_batchFactory = $modelBatchFactory;
        $this->_dirList = $filesystemDirectoryList;
        $this->_batchDistFactory = $batchDistFactory;
        $this->_bHlpPr = $helperProtectedCode;

        parent::__construct($context);
    }

    protected $_batch;

    public function isVendorEnabled($vendor, $batchType=true, $scheduled=false)
    {
        if ($batchType===true) {
            return $this->isVendorEnabled($vendor, 'export_orders', $scheduled)
                && $this->isVendorEnabled($vendor, 'import_orders', $scheduled)
                && $this->isVendorEnabled($vendor, 'export_stockpo', $scheduled)
                && $this->isVendorEnabled($vendor, 'import_stockpo', $scheduled)
                && $this->isVendorEnabled($vendor, 'import_inventory', $scheduled);
        }
        if (!$scheduled) {
            return $vendor->getData("batch_{$batchType}_method");
        } else {
            return $vendor->getData("batch_{$batchType}_method")=='auto'
                && (!$scheduled || $vendor->getData("batch_{$batchType}_schedule"));
        }
    }

    /**
     * @param $type
     * @param $vendor
     * @param string $status
     * @return \Unirgy\DropshipBatch\Model\Batch
     */
    public function createBatch($type, $vendor, $status='pending')
    {
        if (!$vendor instanceof Vendor) {
            $vendor = $this->_hlp->getVendor($vendor);
        }
        $batch = $this->_batchFactory->create()->addData([
            'batch_type' => $type,
            'batch_status' => $status,
            'vendor_id' => $vendor->getId(),
            'use_custom_template' => $this->_useCustomTemplate,
            'is_all_vendors_import' => $this->_isAllVendorsImport,
        ]);
        return $batch;
    }

    public function importVendorOrdersSFA($vendor, $filename=null)
    {
        return $this->_importVendorOrders($vendor, $filename, true);
    }
    public function importVendorOrders($vendor, $filename=null)
    {
        return $this->_importVendorOrders($vendor, $filename, false);
    }

    protected function _importVendorOrders($vendor, $filename=null, $skipFileActions=false)
    {
        $batch = $this->createBatch('import_orders', $vendor, 'processing')->save();
        $batch->setSkipFileactionsFlag($skipFileActions);
        $batch->generateDists('import_orders', $filename);
        $this->_batch = $batch;
        $batch->importOrders()->save();
        return $batch;
    }

    public function importVendorStockpoSFA($vendor, $filename=null)
    {
        return $this->_importVendorStockpo($vendor, $filename, true);
    }
    public function importVendorStockpo($vendor, $filename=null)
    {
        return $this->_importVendorStockpo($vendor, $filename, false);
    }

    protected function _importVendorStockpo($vendor, $filename=null, $skipFileActions=false)
    {
        $batch = $this->createBatch('import_stockpo', $vendor, 'processing')->save();
        $batch->setSkipFileactionsFlag($skipFileActions);
        $batch->generateDists('import_stockpo', $filename);
        $this->_batch = $batch;
        $batch->importStockpo()->save();
        return $batch;
    }
    
	public function importVendorInventorySFA($vendor, $filename=null)
    {
        return $this->_importVendorInventory($vendor, $filename, true);
    }
    public function importVendorInventory($vendor, $filename=null)
    {
        return $this->_importVendorInventory($vendor, $filename, false);
    }

    protected function _importVendorInventory($vendor, $filename=null, $skipFileActions=false)
    {
        /** @var \Unirgy\DropshipBatch\Model\Batch $batch */
        $batch = $this->createBatch('import_inventory', $vendor, 'processing')
            ->setManualFlag(true)
            ->save();
        $batch->setSkipFileactionsFlag($skipFileActions);
        $batch->generateDists('import_inventory', $filename);
        $this->_batch = $batch;
        $batch->importInventory()->save();
        return $batch;
    }
    
    public function getBatch()
    {
    	return $this->_batch;
    }

    /**
    * Export POs to file
    *
    * @param mixed $vendor
    * @param mixed $filename
    * @param mixed $condCb callback to set conditions on $pos collection, pending POs if null
    */
    public function exportVendorOrders($vendor, $filename=null, $condCb=null)
    {
        if (!$vendor instanceof Vendor) {
            $vendor = $this->_hlp->getVendor($vendor);
        }
        /** @var \Unirgy\Dropship\Model\ResourceModel\Po\Collection $pos */
        $pos = $this->_hlp->createObj('Unirgy\Dropship\Model\Po')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('udropship_vendor', $vendor->getId());
        if (!$condCb) {
            $pos->addPendingBatchStatusVendorFilter($vendor);
        } else {
            call_user_func($condCb, $pos, $vendor);
        }
        /** @var \Unirgy\DropshipBatch\Model\Batch $batch */
        $batch = $this->createBatch('export_orders', $vendor, 'processing')
            ->save()
            ->generateDists('export_orders', $filename);

        $this->_batch = $batch;

        foreach ($pos as $po) {
            $batch->addPOToExport($po);
        }
        $batch->exportOrders()->save();
        return $batch;
    }

    public function exportVendorStockpo($vendor, $filename=null, $condCb=null)
    {
        if (!$vendor instanceof Vendor) {
            $vendor = $this->_hlp->getVendor($vendor);
        }
        $pos = $this->_hlp->createObj('Unirgy\Dropship\Model\Po')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('ustock_vendor', $vendor->getId());
        if (!$condCb) {
            $pos->addPendingStockpoBatchStatusFilter();
        } else {
            call_user_func($condCb, $pos, $vendor);
        }
        $batch = $this->createBatch('export_stockpo', $vendor, 'processing')
            ->save()
            ->generateDists('export_stockpo', $filename);

        $this->_batch = $batch;

        $stockPos = [];
        $pos->addOrders()->addStockPos();
        foreach ($pos as $po) {
            $stockPoKey = $po->getUstockpoId().'-'.$po->getUstockVendor();
            if (empty($stockPos[$stockPoKey])) {
                if ($po->getUstockpoId()) {
                    $stockPos[$stockPoKey] = $po->getStockPo();
                } else {
                    $stockPos[$stockPoKey] = $this->_hlp->ustockpoHlp()->udpoToStockpo($po);
                }
            }
            $stockPos[$stockPoKey]->addUdpo($po);
        }
        foreach ($stockPos as $stockPo) {
            if (!$stockPo->getId()) $stockPo->save();
        }
        foreach ($pos as $po) {
            $this->addStockPOToExport($po);
        }
        $batch->exportStockpo()->save();
        return $batch;
    }

    protected $_isAllVendorsImport = false;
    public function isAllVendorsImport($isAllVendorsImport)
    {
        $this->_isAllVendorsImport = $isAllVendorsImport;
        return $this;
    }
    protected $_useCustomTemplate = '';
    public function useCustomTemplate($useCustomTemplate)
    {
        $this->_useCustomTemplate = $useCustomTemplate;
        return $this;
    }
    public function processPost()
    {
        $r = $this->_request;
        $allowAllVendors = $this->scopeConfig->isSetFlag('udropship/batch/allow_all_vendors_import', ScopeInterface::SCOPE_STORE);
        $vendor = $this->_hlp->getVendor($r->getParam('vendor_id'));
        $isAllVendors = !$vendor->getId();
        if ($isAllVendors && !$allowAllVendors) {
            throw new \Exception(__('Invalid vendor'));
        }
        if ($isAllVendors && !$this->_useCustomTemplate) {
            throw new \Exception(__('Please select "Use Template"'));
        }

        $dirList = $this->_dirList;
        $baseDir = $dirList->getPath('var');
        $batchDir = 'udbatch';
        $batchAbsDir = $baseDir.DIRECTORY_SEPARATOR.$batchDir;
        /* @var \Magento\Framework\Filesystem\Directory\Write $dirWrite */
        $dirWrite = $this->_hlp->createObj('\Magento\Framework\Filesystem\Directory\WriteFactory')->create($baseDir);
        $dirWrite->create($batchDir);

        $notes = $r->getParam('batch_notes');
        $errors = false;
        switch ($r->getParam('batch_type')) {
        case 'import_orders':
            if (!empty($_FILES['import_orders_upload']['tmp_name'])) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$_FILES['import_orders_upload']['name'];
                @move_uploaded_file($_FILES['import_orders_upload']['tmp_name'], $filename);
                try {
                    $this->importVendorOrdersSFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_orders_textarea')) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.'import_orders-'.date('YmdHis').'.txt';
                @file_put_contents($filename, $r->getParam('import_orders_textarea'));
                try {
                    $this->importVendorOrdersSFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_orders_locations')) {
                try {
                    $this->importVendorOrders($vendor, $r->getParam('import_orders_locations'));
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }
            if ($r->getParam('import_orders_default') && !$isAllVendors) {
                try {
                    $this->importVendorOrders($vendor);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }

            if ($errors) {
                throw new \Exception(__('Errors during importing, please see individual batches for details'));
            }
            break;

        case 'export_orders':
            $batch = $this->createBatch('export_orders', $vendor, 'processing')
                ->save();

            if ($r->getParam('export_orders_default')) {
                $batch->generateDists('export_orders');
            }
            if ($r->getParam('export_orders_locations')) {
                $batch->generateDists('export_orders', $r->getParam('export_orders_locations'), true);
            }
            if ($r->getParam('export_orders_download')) {
                if ($r->getParam('export_orders_download_filename')) {
                    $filename = $r->getParam('export_orders_download_filename');
                } else {
                    $filename = 'export_orders-'.date('YmdHis').'.txt';
                }
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$filename;
                $batch->generateDists('export_orders', $filename, true);
            }

            $this->_batch = $batch;

            $pos = $this->_hlp->createObj('Unirgy\Dropship\Model\Po')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('udropship_vendor', $vendor->getId())
                ->addPendingBatchStatusVendorFilter($vendor)
                ->addOrders();

            foreach ($pos as $po) {
                $batch->addPOToExport($po);
            }

            $batch->exportOrders()->save();
/*
            if ($r->getParam('export_orders_download')) {
                $this->_helperData->sendDownload(basename($filename), file_get_contents($filename), 'text/plain');
            }
*/
            break;

        case 'import_stockpo':
            if (!empty($_FILES['import_stockpo_upload']['tmp_name'])) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$_FILES['import_stockpo_upload']['name'];
                @move_uploaded_file($_FILES['import_stockpo_upload']['tmp_name'], $filename);
                try {
                    $this->importVendorStockpoSFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_stockpo_textarea')) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.'import_stockpo-'.date('YmdHis').'.txt';
                @file_put_contents($filename, $r->getParam('import_stockpo_textarea'));
                try {
                    $this->importVendorStockpoSFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_stockpo_locations')) {
                try {
                    $this->importVendorStockpo($vendor, $r->getParam('import_stockpo_locations'));
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }
            if ($r->getParam('import_stockpo_default')) {
                try {
                    $this->importVendorStockpo($vendor);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }

            if ($errors) {
                throw new \Exception(__('Errors during importing, please see individual batches for details'));
            }
            break;

        case 'export_stockpo':
            $batch = $this->createBatch('export_stockpo', $vendor, 'processing')
                ->save();

            if ($r->getParam('export_stockpo_default')) {
                $batch->generateDists('export_stockpo');
            }
            if ($r->getParam('export_stockpo_locations')) {
                $batch->generateDists('export_stockpo', $r->getParam('export_stockpo_locations'), true);
            }
            if ($r->getParam('export_stockpo_download')) {
                if ($r->getParam('export_stockpo_download_filename')) {
                    $filename = $r->getParam('export_stockpo_download_filename');
                } else {
                    $filename = 'export_stockpo-'.date('YmdHis').'.txt';
                }
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$filename;
                $batch->generateDists('export_stockpo', $filename, true);
            }

            $this->_batch = $batch;

            $stockPoIds = $this->_hlp->createObj('\Unirgy\DropshipStockPo\Model\Po')->getCollection()
                ->addAttributeToFilter('ustock_vendor', $vendor->getId())
                ->addPendingBatchStatusVendorFilter($vendor)
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
                $batch->addStockPOToExport($po);
            }


            $batch->exportStockpo()->save();
/*
            if ($r->getParam('export_stockpo_download')) {
                $this->_helperData->sendDownload(basename($filename), file_get_contents($filename), 'text/plain');
            }
*/
            break;


            
        case 'import_inventory':
            if (!empty($_FILES['import_inventory_upload']['tmp_name'])) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$_FILES['import_inventory_upload']['name'];
                @move_uploaded_file($_FILES['import_inventory_upload']['tmp_name'], $filename);
                try {
                    $this->importVendorInventorySFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_inventory_textarea')) {
                $filename = $batchAbsDir.DIRECTORY_SEPARATOR.'import_inventory-'.date('YmdHis').'.txt';
                @file_put_contents($filename, $r->getParam('import_inventory_textarea'));
                try {
                    $this->importVendorInventorySFA($vendor, $filename);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
                $this->_batch->setSkipFileactionsFlag(false);
            }
            if ($r->getParam('import_inventory_locations')) {
                try {
                    $this->importVendorInventory($vendor, $r->getParam('import_inventory_locations'));
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }
            if ($r->getParam('import_inventory_default')) {
                try {
                    $this->importVendorInventory($vendor);
                    $this->_batch->setStatus('success');
                } catch (\Exception $e) {
                    if ($this->_batch) {
                        $this->_batch->setBatchStatus('error')->setErrorInfo($e->getMessage());
                    }
                    $errors = true;
                }
                if ($this->_batch) {
                    $this->_batch->setNotes($notes)->save();
                }
            }

            if ($errors) {
                throw new \Exception(__('Errors during importing, please see individual batches for details'));
            }
            break;
            

        default:
            throw new \Exception(__('Invalid batch type'));
        }
    }

    public function getManualImportTemplates($store=null)
    {
        $importTpls = $this->scopeConfig->getValue('udropship/batch/manual_import_templates', ScopeInterface::SCOPE_STORE, $store);
        $importTpls = $this->_hlp->unserialize($importTpls);
        return $importTpls;
    }

    public function getManualImportTemplateTitles($store=null)
    {
        $importTpls = $this->getManualImportTemplates($store);
        $_importTpls = [];
        if (is_array($importTpls)) {
            foreach ($importTpls as $imtpl) {
                $_importTpls[] = @$imtpl['title'];
            }
        }
        return array_unique(array_filter($_importTpls));
    }

    public function getManualImportTemplate($title, $store=null)
    {
        $tpl = false;
        $importTpls = $this->getManualImportTemplates($store);
        if (is_array($importTpls)) {
            foreach ($importTpls as $imtpl) {
                if ($title == @$imtpl['title']) {
                    $tpl = $imtpl['template'];
                    break;
                }
            }
        }
        return $tpl;
    }

    public function getManualExportTemplates($store=null)
    {
        $exportTpls = $this->scopeConfig->getValue('udropship/batch/manual_export_templates', ScopeInterface::SCOPE_STORE, $store);
        $exportTpls = $this->_hlp->unserialize($exportTpls);
        return $exportTpls;
    }

    public function getManualExportTemplateTitles($store=null)
    {
        $exportTpls = $this->getManualExportTemplates($store);
        $_exportTpls = [];
        if (is_array($exportTpls)) {
            foreach ($exportTpls as $extpl) {
                $_exportTpls[] = @$extpl['title'];
            }
        }
        return array_unique(array_filter($_exportTpls));
    }

    public function getManualExportTemplate($title, $field, $store=null)
    {
        $tpl = false;
        $exportTpls = $this->getManualExportTemplates($store);
        if (is_array($exportTpls)) {
            foreach ($exportTpls as $extpl) {
                if ($title == @$extpl['title']) {
                    $tpl = $extpl[$field];
                    break;
                }
            }
        }
        return $tpl;
    }

    public function retryDists($distIds)
    {
        $dists = $this->_batchDistFactory->create()->getCollection()
            ->addFieldToFilter('dist_id', ['in'=>$distIds]);
        $batchIds = [];
        foreach ($dists as $dist) {
            $batchIds[$dist->getBatchId()][] = $dist->getId();
        }
        $batches = $this->_batchFactory->create()->getCollection()
            ->addFieldToFilter('batch_id', ['in'=>array_keys($batchIds)]);
        foreach ($batches as $batch) {
            $batch->retry($batchIds[$batch->getId()]);
        }
        return $this;
    }


    public function generateSchedules()
    {
        $this->_bHlpPr->generateSchedules();
        return $this;
    }

    public function cleanupSchedules()
    {
        return $this;
    }
}