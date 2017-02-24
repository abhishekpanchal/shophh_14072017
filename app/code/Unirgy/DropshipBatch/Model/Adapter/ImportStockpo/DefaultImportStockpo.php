<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ImportStockpo;

use Magento\Framework\Event\ManagerInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\Dropship\Model\PoFactory;

class DefaultImportStockpo extends AbstractImportStockpo
{
    /**
     * @var HelperData
     */
    protected $_bHlp;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    public function __construct(
        HelperData $batchHelper,
        ManagerInterface $eventManager,
        PoFactory $poFactory,
        array $data = []
    )
    {
        $this->_bHlp = $batchHelper;
        $this->_eventManager = $eventManager;
        $this->_poFactory = $poFactory;

        parent::__construct($data);
    }

    public function init()
    {}
    public function parse($content)
    {
        $hlp = $this->_bHlp;

        $fp = fopen('php://temp', 'r+');
        fwrite($fp, $content);
        rewind($fp);

        $fields = $this->getImportFields();
        $rows = [];
        $idx = 0;
        while (!feof($fp)) {
            $r = @fgetcsv($fp, 0, $this->getVendor()->getBatchImportStockpoFieldDelimiter(), '"');
            if (!$idx++ && $this->getVendor()->getBatchImportStockpoSkipHeader()) continue;
            if (!$r) {
                $rows[] = ['error'=>__('Invalid row format')];
                continue;
            }
            $row = [];
            foreach ($r as $i=>$v) {
                if (isset($fields[$i])) {
                    $row[$fields[$i]] = $v;
                }
            }
            $rows[] = $row;
        }
        fclose($fp);

        return $rows;
    }

    public function initImportFields()
    {
        return $this->_initImportFields(true);
    }
    protected $_initImportFields;
    protected function _initImportFields($refresh=false)
    {
        if (is_null($this->_initImportFields) || $refresh) {
            $tpl = $this->getVendor()->getBatchImportStockpoTemplate();
            $this->setData('import_template', $tpl);
            $this->getBatch()->setData('import_template', $tpl);
            if (!preg_match_all('#\[([^]]+)\]([^[]+)?#', $tpl, $m, PREG_PATTERN_ORDER)) {
                throw new \Exception('Invalid import template');
            }
            if (!in_array('stockpo_id', $m[1])) {
                throw new \Exception('Missing required field');
            }
            $this->setData('import_fields', $m[1]);
            $this->getBatch()->setData('import_fields', $m[1]);
            $this->setData('import_delimiter', $m[2][0]);
            $this->getBatch()->setData('import_delimiter', $m[2][0]);
            $this->_initImportFields = true;
        }
        return $this;
    }

    public function getImportFields()
    {
        $this->_initImportFields();
        return $this->getData('import_fields');
    }

    protected function _validateRows(&$rows)
    {
        $hlp = $this->_bHlp;
        $allowDupTrackIds = false;
        $poIds = [];
        $orderIds = [];
        $trackIds = [];
        foreach ($rows as $i=>&$r) {
            if (!empty($r['error'])) {
                continue;
            }
            if (empty($r['stockpo_id'])) {
                $r['error'] = __('Missing required field');
                continue;
            }
            $stockPoIds[$r['stockpo_id']] = $i;
        }
        unset($r);

        return $stockPoIds;
    }

    public function process($rows)
    {
        $markAsShipped = in_array(
            $this->getVendor()->getData('batch_import_stockpo_po_status'),
            $this->getBatch()->getMarkAsShippedStatuses()
        );

        $hlp = $this->_bHlp;
        $allowDupTrackIds = false;

        $this->_eventManager->dispatch(
            'udbatch_import_stockpo_convert_before',
            ['batch'=>$this->getBatch(), 'adapter'=>$this, 'vars'=>['rows'=>&$rows]]
        );

        $poIds = $this->_validateRows($rows);

        // find POs and orders
        $pos = $this->_poFactory->create()->getCollection()
            ->addAttributeToFilter('ustock_vendor', $this->getVendorId())
            ->addAttributeToFilter('ustockpo_increment_id', ['in'=>array_keys($poIds)])
            ->addOrders()
            ->addStockPos();

        foreach ($pos as $po) {
            $order = $po->getOrder();
            $r = $rows[$poIds[$po->getUstockpoIncrementId()]];
            $this->getBatch()->addImportRowLog($order, $po, $r);
        }

        return $this;
    }

}
