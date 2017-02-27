<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ImportInventory;

use Magento\Eav\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Helper\Data as DropshipBatchHelperData;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\Dropship\Helper\Data as HelperData;

class DefaultImportInventory extends AbstractImportInventory
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipBatchHelperData
     */
    protected $_bHlp;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Config
     */
    protected $_eavConfig;

    public function __construct(
        HelperData $udropshipHelper,
        DropshipBatchHelperData $batchHelper,
        ManagerInterface $eventManager,
        ScopeConfigInterface $scopeConfig,
        Config $eavConfig,
        array $data = []
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_bHlp = $batchHelper;
        $this->_eventManager = $eventManager;
        $this->scopeConfig = $scopeConfig;
        $this->_eavConfig = $eavConfig;

        parent::__construct($data);
    }

    public function init()
    {}
    public function import($content)
    {
        $this->init();
        $rows = $this->parse($content);
        if ('realtime' != $this->getVendor()->getBatchImportInventoryReindex()
            && $this->_hlp->isUdmultiAvailable()
        ) {
            $this->_hlp->udmultiHlp()->setReindexFlag(false);
        }
        while (!empty($rows)) {
            $rowsToProcess = array_splice($rows, 0, 1000);
            $this->process($rowsToProcess);
            $this->getBatch()->flushRowsLog();
            if ($this->_hlp->isUdmultiAvailable()) {
                $this->_hlp->udmultiHlp()->clearMultiVendorData();
            }
        }
        if ($this->_hlp->isUdmultiAvailable()) {
            $this->_hlp->udmultiHlp()->setReindexFlag(true);
        }
        /* @var \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry */
        $indexerRegistry = $this->_hlp->getObj('\Magento\Framework\Indexer\IndexerRegistry');
        /* @var \Magento\Indexer\Model\Config $indexerConfig */
        $indexerConfig = $this->_hlp->getObj('\Magento\Indexer\Model\Config');

        if ('full' == $this->getVendor()->getBatchImportInventoryReindex()) {
            foreach ([
                 \Unirgy\Dropship\Model\Indexer\ProductVendorAssoc\Processor::INDEXER_ID,
                 \Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID,
                 \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID,
             ] as $indexerId) {
                if (!$indexerConfig->getIndexer($indexerId)) continue;
                $indexer = $indexerRegistry->get($indexerId);
                if ($indexer && !$indexer->isScheduled()) {
                    $indexer->reindexAll();
                }
            }
        } elseif ('manual' == $this->getVendor()->getBatchImportInventoryReindex()) {
            foreach ([
                 \Unirgy\Dropship\Model\Indexer\ProductVendorAssoc\Processor::INDEXER_ID,
                 \Magento\CatalogInventory\Model\Indexer\Stock\Processor::INDEXER_ID,
                 \Magento\Catalog\Model\Indexer\Product\Price\Processor::INDEXER_ID,
             ] as $indexerId) {
                if (!$indexerConfig->getIndexer($indexerId)) continue;
                $indexer = $indexerRegistry->get($indexerId);
                $indexer->invalidate();
            }
        }
        return $this;
    }
    public function parse($content)
    {
        $hlp = $this->_bHlp;

        $_content = preg_split("/\r\n|\n\r|\r|\n/", $content);
        if ($_content !== false) {
            $content = implode("\n", $_content);
        }

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $content);
        rewind($fp);

        $fields = $this->getInvImportFields();
        $rows = [];
        $idx = 0;
        while (!feof($fp)) {
            $r = @fgetcsv($fp, 0, $this->getVendor()->getBatchImportInventoryFieldDelimiter(), '"');
            if (!$idx++ && $this->getVendor()->getBatchImportInventorySkipHeader()) continue;
            if (!$r) {
                $rows[] = ['error'=>__('Invalid row format')];
                continue;
            }
            $row = [];
            foreach ($r as $i=>$v) {
                if (isset($fields[$i]) && $this->_isAllowedField($fields[$i])) {
                    $row[$fields[$i]] = trim($v);
                }
            }
            $rows[] = $row;
        }
        fclose($fp);

        return $rows;
    }

    public function process($rows)
    {
        $hlp = $this->_bHlp;

        $this->_eventManager->dispatch(
            'udbatch_import_inventory_convert_before',
            ['batch'=>$this->getBatch(), 'adapter'=>$this, 'vars'=>['rows'=>&$rows]]
        );

        $vsAttr = $this->scopeConfig->getValue('udropship/vendor/vendor_sku_attribute', ScopeInterface::SCOPE_STORE);
        $allowVendorSku = $this->_hlp->isUdmultiAvailable() || (!empty($vsAttr) && $vsAttr != 'sku');

        if (!empty($vsAttr)) {
            $vsAttr = $this->_eavConfig->getAttribute('catalog_product', $vsAttr);
        }

        $skus = [];
        $vendorSkus = [];
        foreach ($rows as $i=>&$r) {
            if (!empty($r['error'])) {
                continue;
            }
            if (!empty($r['stock_qty'])) {
                $r['stock_qty'] = trim($r['stock_qty']);
            }
            if (!empty($r['stock_qty_add'])) {
                $r['stock_qty_add'] = trim($r['stock_qty_add']);
            }
            if (empty($r['sku']) && (!$allowVendorSku || empty($r['vendor_sku']))) {
                if ($allowVendorSku) {
                    $r['error'] = __('Missing required field: sku or vendor_sku');
                } else {
                    $r['error'] = __('Missing required field: sku');
                }
                continue;
            }
            if (!empty($r['sku'])) {
                if (array_key_exists($r['sku'], $skus)) {
                    $r['error'] = __('Duplicate sku within file');
                    continue;
                }
                $skus[$r['sku']] = $i;
            } elseif ($allowVendorSku && !empty($r['vendor_sku'])) {
                if (!empty($vendorSkus[$r['vendor_sku']])) {
                    $r['error'] = __('Duplicate vendor sku within file');
                    continue;
                }
                $vendorSkus[$r['vendor_sku']] = $i;
            }
            $r['use_reserved_qty'] = $this->getVendor()->getData("batch_import_inventory_useinventory_reserved_qty");
        }
        unset($r);

        $_res = $this->_hlp->rHlp();
        $_readCon = $_res->getConnection();
        $_writeCon = $_res->getConnection();
        $_skus = [];
        foreach ($skus as $sku=>$p) {
            $_skus[] = is_numeric($sku) ? "'".$sku."'" : $_readCon->quote($sku);
        }
        $_vendorSkus = [];
        foreach ($vendorSkus as $sku=>$p) {
            $_vendorSkus[] = is_numeric($sku) ? "'".$sku."'" : $_readCon->quote($sku);
        }
        $skuPids = [];
        $skuPidUnions = [];
        if (!empty($_skus)) {
            $skuPidUnions[] = $_readCon->select()
                ->from($_res->getTable('catalog_product_entity'), ['sku', 'entity_id'])
                ->where('sku in ('.join(',', $_skus).')');
        }
        if (!empty($_vendorSkus) && $allowVendorSku) {
            $_tmpSel = $_readCon->select()
                ->from(['p' => $_res->getTable('catalog_product_entity')], []);
            if ($this->_hlp->isUdmultiAvailable()) {
                $_tmpSel->join(
                    ['vp' => $_res->getTable('udropship_vendor_product')],
                    'vp.product_id=p.entity_id and vp.vendor_id='.$this->getVendorId(),
                    []
                );
                $_tmpSel->where('vp.vendor_sku in ('.join(',', $_vendorSkus).')');
                $_tmpSel->columns(['vp.vendor_sku', 'GROUP_CONCAT(p.entity_id)']);
                $_tmpSel->group('vp.vendor_sku');
            } else {
                $_tmpSel->join(
                    ['vp' => $vsAttr->getBackendTable()],
                    'vp.'.$this->_hlp->rowIdField().'=p.'.$this->_hlp->rowIdField().' and vp.store_id=0 and vp.attribute_id='.$vsAttr->getId(),
                    []
                );
                $_tmpSel->where('vp.value in ('.join(',', $_vendorSkus).')');
                $_tmpSel->columns(['vp.value', 'GROUP_CONCAT(p.entity_id)']);
                $_tmpSel->group('vp.value');
            }
            $skuPidUnions[] = $_tmpSel;
        }
        if (!empty($skuPidUnions)) {
            foreach ($skuPidUnions as &$skuPidUnion) {
                $skuPidUnion = "($skuPidUnion)";
            }
            unset($skuPidUnion);
            $skuPidUnionSel = $_readCon->select()->union($skuPidUnions);
	        $skuPids = $_readCon->fetchPairs($skuPidUnionSel);
        }

        $newAssocCfg = $this->scopeConfig->getValue('udropship/batch/invimport_allow_new_association', ScopeInterface::SCOPE_STORE);
        $newAssocAllowed = $this->_hlp->isUdmultiAvailable()
            && ($newAssocCfg==Source::NEW_ASSOCIATION_YES
                || $newAssocCfg==Source::NEW_ASSOCIATION_YES_MANUAL && $this->getBatch()->getManualFlag()
            );

        $newAssocSql = [];
        $updateRequest = [];
        $vskuMultipid = $this->scopeConfig->getValue('udropship/batch/invimport_vsku_multipid', ScopeInterface::SCOPE_STORE);
        foreach ($rows as $i=>&$r) {
            $skuKey = !empty($r['sku']) ? 'sku' : 'vendor_sku';
            $_newAssocAllowed = $skuKey=='sku' && $newAssocAllowed;
            $isVsKey = $skuKey == 'vendor_sku';
            $_pIdsStr = @$skuPids[$r[$skuKey]];
            $_pIds = explode(',', $_pIdsStr);
            $_pIdsCnt = count($_pIds);
            if (empty($r['sku']) && empty($r['vendor_sku'])) {
                $r['error'] = __('Neither sku or vendor_sku specified');
            } elseif (empty($r[$skuKey]) || empty($skuPids[$r[$skuKey]])) {
        		$r['error'] = __('Product not found for '.($isVsKey ? 'vendor ' : '').'sku "%1"', $r[$skuKey]);
            } elseif ($_pIdsCnt>1
                && $vskuMultipid == Source::INVIMPORT_VSKU_MULTIPID_REPORT
            ) {
                $r['error'] = __('Vendor sku "%1" maps to multiple products (ids: "%2")', $r[$skuKey], $_pIdsStr);
        	} elseif ($this->_hlp->isUdmultiAvailable()
                && $_pIdsCnt==1
                && !in_array($skuPids[$r[$skuKey]], $this->getVendor()->getVendorTableProductIds())
            ) {
                if (!$_newAssocAllowed) {
        		    $r['error'] = __('Product with '.($isVsKey ? 'vendor ' : '').'sku "%1" does not associate with vendor', $r[$skuKey]);
                } else {
                    $newAssocSql[] = $_readCon->quote([
                        'product_id' => $skuPids[$r[$skuKey]],
                        'vendor_id' => $this->getVendor()->getId(),
                        'status' => $this->_hlp->udmultiHlp()->getDefaultMvStatus()
                    ]);
                    $r['new_assoc'] = true;
                    if (count($newAssocSql)>1000) {
                        $_writeCon->query(sprintf(
                            "insert ignore into %s (product_id,vendor_id,status) values (%s)",
                            $_res->getTable('udropship_vendor_product'),
                            implode('),(', $newAssocSql)
                        ));
                        $newAssocSql = [];
                    }
                }
        	}
            foreach ($this->_getNumericFields() as $decKey) {
                if (!empty($r[$decKey])) {
                    $r[$decKey] = $this->_hlp->formatNumber($r[$decKey]);
                }
            }
            $r['product_id'] = !empty($r[$skuKey]) && !empty($skuPids[$r[$skuKey]]) ? $skuPids[$r[$skuKey]] : null;
            $this->getBatch()->addInvImportRowLog($r);
            if (empty($r['error'])) {
                foreach ($_pIds as $_pId) {
                    $_uRow = $rows[$i];
                    $_uRow['product_id'] = $_pId;
            	    $updateRequest[$_pId] = $_uRow;
                    if ($vskuMultipid == Source::INVIMPORT_VSKU_MULTIPID_FIRST) break;
                }
            }
        }
        unset($r);

        if ($newAssocAllowed && $newAssocSql) {
            $_writeCon->query(sprintf(
                "insert ignore into %s (product_id,vendor_id,status) values (%s)",
                $_res->getTable('udropship_vendor_product'),
                implode('),(', $newAssocSql)
            ));
        }

        if (!empty($updateRequest)) {
        	if ($this->_hlp->isUdmultiAvailable()) {
                $this->_hlp->udmultiHlp()->saveThisVendorProductsPidKeys($updateRequest, $this->getVendor());
            }
            if (!$this->_hlp->isUdmultiActive()) {
                $this->_hlp->saveThisVendorProducts($updateRequest, $this->getVendor());
            }
        }

        return $this;
    }

    protected $_allFields;
    protected function _initAllFields()
    {
        if (null === $this->_allFields) {
            $this->_allFields = ['vendor_sku'=>0, 'vendor_cost'=>1, 'stock_qty'=>1, 'sku'=>0, 'stock_status'=>1];
            if ($this->_hlp->isUdmultiActive()) {
                /* @var \Unirgy\DropshipMulti\Helper\ProductFields $multiProdHlp */
                $multiProdHlp = ObjectManager::getInstance()->get('\Unirgy\DropshipMulti\Helper\ProductFields');
                $this->_allFields = array_merge($this->_allFields, $multiProdHlp->getFieldsByKeys());
            }
        }
        return $this;
    }
    protected function _getAllFields()
    {
        $this->_initAllFields();
        return $this->_allFields;
    }

    protected function _isAllowedField($field)
    {
        return array_key_exists($field, $this->_getAllFields());
    }
    protected $__numericFields;
    protected $_numericFields;
    protected function _initNumericFields()
    {
        if (null === $this->__numericFields) {
            foreach ($this->_getAllFields() as $field=>$isNum) {
                if ($isNum) $this->__numericFields[$field] = $isNum;
            }
            $this->_numericFields = array_keys($this->__numericFields);
        }
        return $this;
    }
    protected function _isNumericField($field)
    {
        return array_key_exists($field, $this->__numericFields);
    }
    protected function _getNumericFields()
    {
        $this->_initNumericFields();
        return $this->_numericFields;
    }

    public function initInvImportFields()
    {
        return $this->_initInvImportFields(true);
    }
    protected $_initInvImportFields;
    protected function _initInvImportFields($refresh=false)
    {
        if (is_null($this->_initInvImportFields) || $refresh) {
            $tpl = $this->getVendor()->getBatchImportInventoryTemplate();
            if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())
                && ($_custTpl = $this->_bHlp->getManualImportTemplate($_useCustTpl))
            ) {
                $tpl = $_custTpl;
            }
            $this->setData('inv_import_template', $tpl);
            $this->getBatch()->setData('inv_import_template', $tpl);
            if (!preg_match_all('#\[([^]]+)\]([^[]+)?#', $tpl, $m, PREG_PATTERN_ORDER)) {
                throw new \Exception('Invalid import template');
            }
            if (!in_array('sku', $m[1]) && !in_array('vendor_sku', $m[1])) {
                throw new \Exception('Missing required field');
            }
            $this->setData('inv_import_fields', $m[1]);
            $this->getBatch()->setData('inv_import_fields', $m[1]);
            $this->setData('inv_import_delimiter', $m[2][0]);
            $this->getBatch()->setData('inv_import_delimiter', $m[2][0]);
            $this->_initImportFields = true;
        }
        return $this;
    }

    public function getInvImportFields()
    {
        $this->_initInvImportFields();
        return $this->getData('inv_import_fields');
    }

}
