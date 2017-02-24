<?php

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipBatch\Helper\Data as DropshipBatchHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;

class ImportStockPost extends AbstractBatch
{
    /**
     * @var DropshipBatchHelperData
     */
    protected $_bHlp;

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @var DirectoryList
     */
    protected $_dirList;

    public function __construct(
        DropshipBatchHelperData $batchHelper,
        Config $eavConfig,
        DirectoryList $dirList,
        Context $context,
        ScopeConfigInterface $scopeConfig,
        DesignInterface $viewDesignInterface,
        StoreManagerInterface $storeManager,
        LayoutFactory $viewLayoutFactory,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        HelperData $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\HTTP\Header $httpHeader
    )
    {
        $this->_bHlp = $batchHelper;
        $this->_eavConfig = $eavConfig;
        $this->_dirList = $dirList;

        parent::__construct($context, $scopeConfig, $viewDesignInterface, $storeManager, $viewLayoutFactory, $registry, $resultForwardFactory, $helper, $resultPageFactory, $resultRawFactory, $httpHeader);
    }

	public function execute()
    {
    	$r = $this->getRequest();
    	$hlp = $this->_hlp;
    	$bHlp = $this->_bHlp;

        $vsAttr = $this->scopeConfig->getValue('udropship/vendor/vendor_sku_attribute', ScopeInterface::SCOPE_STORE);
        $allowVendorSku = $this->_hlp->isUdmultiAvailable() || (!empty($vsAttr) && $vsAttr != 'sku');

        if (!empty($vsAttr)) {
            $vsAttr = $this->_eavConfig->getAttribute('catalog_product', $vsAttr);
        }

    	try {
            $dirList = $this->_dirList;
            $baseDir = $dirList->getPath('var');
            $batchDir = 'udbatch';
            $batchAbsDir = $baseDir.DIRECTORY_SEPARATOR.$batchDir;
            /* @var \Magento\Framework\Filesystem\Directory\Write $dirWrite */
            $dirWrite = $this->_hlp->createObj('\Magento\Framework\Filesystem\Directory\WriteFactory')->create($baseDir);
            $dirWrite->create($batchDir);

	    	if (!empty($_FILES['import_inventory_upload']['tmp_name'])) {
	            $filename = $batchAbsDir.DIRECTORY_SEPARATOR.$_FILES['import_inventory_upload']['name'];
	            @move_uploaded_file($_FILES['import_inventory_upload']['tmp_name'], $filename);
	        }
	        if ($r->getParam('import_inventory_textarea')) {
	            $filename = $batchAbsDir.DIRECTORY_SEPARATOR.'import_inventory-'.date('YmdHis').'.txt';
	            @file_put_contents($filename, $r->getParam('import_inventory_textarea'));
	        }
	        if (!isset($filename)) {
	        	throw new \Exception(__('Empty input'));
	        }
        	if (!($delimiter = $r->getParam('import_inventory_delimiter'))) {
        		throw new \Exception(__('Empty delimiter'));
        	}
        	if (!($enclosure = $r->getParam('import_inventory_enclosure'))) {
        		throw new \Exception(__('Empty enclosure'));
        	}
        	if (!($fields = $r->getParam('import_inventory_fields')) || !is_array($fields)) {
        		throw new \Exception(__('Empty fields'));
        	}
        	if (!in_array('sku', $fields) && (!$allowVendorSku || !in_array('vendor_sku', $fields))) {
                if ($allowVendorSku) {
        		    throw new \Exception(__('Required "%1" (or "%2") field is not specified', 'sku', 'vendor_sku'));
                } else {
                    throw new \Exception(__('Required "%1" field is not specified', 'sku'));
                }
        	}
        	foreach ($fields as $i=>$field) {
        		if (!empty($field) && !$this->isAllowedField($fields[$i])) {
        			$this->messageManager->addNotice(__('"%1" field is not allowed. will be ignored.', $field));
        		}
        	}
            $content = file_get_contents($filename);

            $_content = preg_split("/\r\n|\n\r|\r|\n/", $content);
            if ($_content !== false) {
                $content = implode("\n", $_content);
            }

            $fp = fopen('php://temp', 'r+');
            fwrite($fp, $content);
            rewind($fp);

        	$rows = [];
        	while (!feof($fp)) {
	            $row = @fgetcsv($fp, 0, $delimiter, $enclosure);
	            if ($row === false && feof($fp)) break;
	            if ($row === false) {
	                $rows[] = false;
	                continue;
	            }
	            $fRow = array_filter($row);
	            if (empty($fRow)) {
	            	$rows[] = '';
	                continue;
	            }
	            $_row = [];
	            foreach ($row as $i=>$v) {
	                if (!empty($fields[$i]) && $this->isAllowedField($fields[$i])) {
	                    $_row[$fields[$i]] = $v;
	                }
	            }
	            $rows[] = $_row;
	        }
	        fclose($fp);
	        $duplicateSku = $missingReqField = $invalidRows = [];
	        $skus = [];
            $vendorSkus = [];
	    	foreach ($rows as $i=>$r) {
	            if ($r === false) {
	            	$invalidRows[] = $i+1;
	                continue;
	            }
	            if ($r === '') {
	            	$emptyRows[] = $i+1;
	            	continue;
	            }
	            if (empty($r['sku']) && (!$allowVendorSku || empty($r['vendor_sku']))) {
	            	$missingReqField[] = $i+1;
	            	continue;
	            }
                if (!empty($r['sku'])) {
                    if (!empty($skus[$r['sku']])) {
                        $duplicateSku[] = $i+1;
                        continue;
                    }
                    $skus[$r['sku']] = $i;
                } elseif (!empty($r['vendor_sku'])) {
                    if (!empty($vendorSkus[$r['vendor_sku']])) {
                        $duplicateSku[] = $i+1;
                        continue;
                    }
                    $vendorSkus[$r['vendor_sku']] = $i;
                }
	        }
	        $_res  = $this->_hlp->rHlp();
	        $_conn = $_res->getConnection();

	    	$_skus = [];
            foreach ($skus as $sku=>$p) {
                $_skus[] = is_numeric($sku) ? "'".$sku."'" : $_conn->quote($sku);
            }
            $_vendorSkus = [];
            foreach ($vendorSkus as $sku=>$p) {
                $_vendorSkus[] = is_numeric($sku) ? "'".$sku."'" : $_conn->quote($sku);
            }
            $skuPids = [];
            $skuPidUnions = [];
            if (!empty($_skus)) {
                $skuPidUnions[] = $_conn->select()
                    ->from($_res->getTable('catalog_product_entity'), ['sku', $this->_hlp->rowIdField()])
                    ->where('sku in ('.join(',', $_skus).')');
            }
            if (!empty($_vendorSkus) && $allowVendorSku) {
                $_tmpSel = $_conn->select()
                    ->from(['p' => $_res->getTable('catalog_product_entity')], []);
                if ($this->_hlp->isUdmultiAvailable()) {
                    $_tmpSel->join(
                        ['vp' => $_res->getTable('udropship_vendor_product')],
                        'vp.product_id=p.'.$this->_hlp->rowIdField().' and vp.vendor_id='.$this->_getSession()->getVendor()->getId(),
                        []
                    );
                    $_tmpSel->where('vp.vendor_sku in ('.join(',', $_vendorSkus).')');
                    $_tmpSel->columns(['vp.vendor_sku', $this->_hlp->rowIdField()]);
                } else {
                    $_tmpSel->join(
                        ['vp' => $vsAttr->getBackendTable()],
                        'vp.'.$this->_hlp->rowIdField().'=p.'.$this->_hlp->rowIdField().' and vp.store_id=0 and vp.attribute_id='.$vsAttr->getId(),
                        []
                    );
                    $_tmpSel->where('vp.value in ('.join(',', $_vendorSkus).')');
                    $_tmpSel->columns(['vp.value', $this->_hlp->rowIdField()]);
                }
                $skuPidUnions[] = $_tmpSel;
            }
            if (!empty($skuPidUnions)) {
                $skuPidUnionSel = $_conn->select()->union($skuPidUnions);
                $skuPids = $_conn->fetchPairs($skuPidUnionSel);
            }

	        $notFoundProducts = $notAssociatedProducts = [];
	        $updateRequest = [];
	        foreach ($rows as $i=>&$r) {
	        	if (empty($r)) continue;
                $skuKey = !empty($r['sku']) ? 'sku' : 'vendor_sku';
	        	if (empty($skuPids[$r[$skuKey]])) {
	        		$notFoundProducts[] = $i+1;
	        		continue;
	        	}
		        if (!in_array($skuPids[$r[$skuKey]], $this->_getSession()->getVendor()->getAssociatedProductIds())) {
		        	$notAssociatedProducts[] = $i+1;
		        	continue;
	        	}
	            $updateRequest[$skuPids[$r[$skuKey]]] = $rows[$i];
	        }
	        unset($r);

	        if (!empty($invalidRows)) {
	        	$this->messageManager->addError(__('Invalid rows: %1', implode(',', $invalidRows)));
	        }
    		if (!empty($emptyRows)) {
	        	$this->messageManager->addError(__('Empty rows: %1', implode(',', $emptyRows)));
	        }
    		if (!empty($missingReqField)) {
	        	$this->messageManager->addError(__('Missing required field in rows: %1', implode(',', $missingReqField)));
	        }
    		if (!empty($duplicateSku)) {
	        	$this->messageManager->addError(__('Duplicate sku within file in rows: %1', implode(',', $duplicateSku)));
	        }
    		if (!empty($notFoundProducts)) {
	        	$this->messageManager->addError(__('Product not found for sku in rows: %1', implode(',', $notFoundProducts)));
	        }
    		if (!empty($notAssociatedProducts)) {
	        	$this->messageManager->addError(__('Products not associated with vendor in rows: %1', implode(',', $notAssociatedProducts)));
	        }

	        $cnt = 0;
    		if (!empty($updateRequest)) {
                if ($this->_hlp->isUdmultiAvailable()) {
                    $cnt = $this->_hlp->udmultiHlp()->saveThisVendorProductsPidKeys($updateRequest, $this->_getSession()->getVendor());
                }
                if (!$this->_hlp->isUdmultiActive()) {
                    $cnt = $this->_hlp->saveThisVendorProducts($updateRequest, $this->_getSession()->getVendor());
                }
	        }
    		if ($cnt) {
                $this->messageManager->addSuccess(__($cnt==1 ? '%1 product was updated' : '%1 products were updated', $cnt));
            } else {
                $this->messageManager->addNotice(__('No updates were made'));
            }

    	} catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->_redirect('udbatch/vendor_batch/importStock');
    }
}
