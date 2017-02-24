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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order\Item;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Model\Batch\DistFactory;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\PoFactory;

class Batch extends AbstractModel
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DistFactory
     */
    protected $_batchDistFactory;

    /**
     * @var Io
     */
    protected $_modelIo;

    /**
     * @var PoFactory
     */
    protected $_modelPoFactory;

    public function __construct(Context $context,
        Registry $registry, 
        ScopeConfigInterface $configScopeConfigInterface, 
        HelperData $helperData, 
        DistFactory $batchDistFactory, 
        Io $modelIo, 
        PoFactory $modelPoFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_scopeConfig = $configScopeConfigInterface;
        $this->_hlp = $helperData;
        $this->_batchDistFactory = $batchDistFactory;
        $this->_modelIo = $modelIo;
        $this->_modelPoFactory = $modelPoFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected $_eventPrefix = 'udbatch_batch';
    protected $_eventObject = 'batch';

    protected $_templateFilter;
    protected $_firstTimeSave = false;

    protected $_adapter;

    protected $_distsCollection;

    protected function _construct()
    {
        $this->_init('Unirgy\DropshipBatch\Model\ResourceModel\Batch');
    }

    public function getAdapter($reset=false)
    {
        if (!$this->_adapter || $reset) {
            $code = $this->getAdapterType();
            if (!$code) {
                $code = 'default';
            }
            $model = $this->_hlp->config()->getBatchAdapter($this->getBatchType(), $code, 'model');
            if (!$model) {
                throw new \Exception(__('Invalid model for %1 (%2)', $this->getBatchType().'/'.$code, $model));
            }
            $this->_adapter = ObjectManager::getInstance()->create($model);
            $this->_adapter->setBatch($this);
        }
        return $this->_adapter;
    }

    public function getVendor()
    {
        return $this->_hlp->getVendor($this->getVendorId());
    }

    public function getDatetimeFilter()
    {
        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $result = false;
        $vendor = $this->getVendor();
        if ($this->getBatchType()=='import_orders') {
            if ($vendor->getData('batch_import_orders_track_dist_ts')) {
                if (!$vendor->getData('batch_import_orders_ts_newts')) {
                    $vendor->setData('batch_import_orders_ts_newts', $now);
                }
                $result = [
                    $vendor->getData('batch_import_orders_ts'),
                    $vendor->getData('batch_import_orders_ts_newts'),
                    $vendor->getData('batch_import_orders_dist_tz'),
                ];
            }
        } elseif ($this->getBatchType()=='import_inventory') {
            if ($vendor->getData('batch_import_inventory_track_dist_ts')) {
                if (!$vendor->getData('batch_import_inventory_ts_newts')) {
                    $vendor->setData('batch_import_inventory_ts_newts', $now);
                }
                $result = [
                    $vendor->getData('batch_import_inventory_ts'),
                    $vendor->getData('batch_import_inventory_ts_newts'),
                    $vendor->getData('batch_import_inventory_dist_tz'),
                ];
            }
        } elseif ($this->getBatchType()=='import_stockpo') {
            if ($vendor->getData('batch_import_stockpo_track_dist_ts')) {
                if (!$vendor->getData('batch_import_stockpo_ts_newts')) {
                    $vendor->setData('batch_import_stockpo_ts_newts', $now);
                }
                $result = [
                    $vendor->getData('batch_import_stockpo_ts'),
                    $vendor->getData('batch_import_stockpo_ts_newts'),
                    $vendor->getData('batch_import_stockpo_dist_tz'),
                ];
            }
        }
        return $result;
    }

    public function getAdapterType()
    {
        if ($this->getBatchType()=='export_orders') {
            $type = $this->getVendor()->getBatchExportOrdersAdapter();
        } elseif ($this->getBatchType()=='import_orders') {
            $type = $this->getVendor()->getBatchImportOrdersAdapter();
        } elseif ($this->getBatchType()=='import_inventory') {
            $type = $this->getVendor()->getBatchImportInventoryAdapter();
        } elseif ($this->getBatchType()=='export_stockpo') {
            $type = $this->getVendor()->getBatchExportStockpoAdapter();
        } elseif ($this->getBatchType()=='import_stockpo') {
            $type = $this->getVendor()->getBatchImportStockpoAdapter();
        } 
        return $type;
    }

    public function getSkipItemStatuses()
    {
        return [
            Item::STATUS_RETURNED,
            Item::STATUS_REFUNDED,
            Item::STATUS_CANCELED,
        ];
    }

    protected $_incToIdMap = [];
    protected $_incToOrderIdMap = [];
    protected $_incToOrderIncIdMap= [];
    public function addPOToExport($po)
    {
        $this->_incToIdMap[$po->getIncrementId()] = $po->getId();
        $this->_incToOrderIdMap[$po->getIncrementId()] = $po->getOrder()->getId();
        $this->_incToOrderIncIdMap[$po->getIncrementId()] = $po->getOrder()->getIncrementId();
        $this->getAdapter()->addPO($po);
        return $this;
    }
    public function addStockPOToExport($po)
    {
        $this->_incToIdMap[$po->getIncrementId()] = $po->getId();
        $this->_incToOrderIdMap[$po->getIncrementId()] = $po->getOrder()->getId();
        $this->_incToOrderIncIdMap[$po->getIncrementId()] = $po->getOrder()->getIncrementId();
        $this->getAdapter()->addPO($po);
        return $this;
    }

    public function addRowLog($order, $po, $item)
    {
        $rowsLog = (array)$this->getRowsLog();
        if (!$this->getRowsLog()) {
            $this->setRowsLog([]);
        }
        $this->_data['rows_log'][] = [
            'order_id' => $order->getId(),
            'po_id' => $po->getId(),
            'item_id' => $item->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'po_increment_id' => $po->getIncrementId(),
            'item_sku' => $item->getSku(),
            'has_error' => 0,
            'row_json' => ['sku'=>$item->getSku(), 'qty'=>$item->getQty(), 'price'=>$item->getPrice()],
        ];
        return $this;
    }

    public function addImportRowLog($order, $po, $r, $track=null)
    {
        $rowsLog = (array)$this->getRowsLog();
        if (!$this->getRowsLog()) {
            $this->setRowsLog([]);
        }
        $this->_data['rows_log'][] = [
            'order_id' => $order->getId(),
            'po_id' => $po->getId(),
            'stockpo_id' => $po->getStockPo() ? $po->getStockPo()->getId() : null,
            'track_id' => !empty($track) && $track->getId() ? $track->getId() : null,
            'order_increment_id' => $order->getIncrementId(),
            'po_increment_id' => $po->getIncrementId(),
            'stockpo_increment_id' => $po->getStockPo() ? $po->getStockPo()->getIncrementId() : null,
            'tracking_id' => !empty($r['tracking_id']) ? $r['tracking_id'] : null,
            'has_error' => !empty($r['error']),
            'error_info' => !empty($r['error']) ? $r['error'] : null,
            'row_json' => $r,
        ];
        return $this;
    }

    public function flushRowsLog()
    {
        if (!$this->getId()) {
            $this->save();
        } else {
            if ($this->getRowsLog()) {
                $this->setNumRows($this->getNumRows()+sizeof($this->getRowsLog()));
            }
            $this->getResource()->flushRowsLog($this);
        }
    }

    public function addInvImportRowLog($r)
    {
        $rowsLog = (array)$this->getRowsLog();
        if (!$this->getRowsLog()) {
            $this->setRowsLog([]);
        }
        if ($this->isSaveInvImportData()) {
            $newRow = [];
            foreach (['product_id', 'sku', 'stock_qty', 'stock_qty_add', 'vendor_sku', 'vendor_cost', 'vendor_price', 'shipping_price', 'state', 'status', 'stock_status', 'vendor_title', 'avail_state', 'avail_date', 'special_price', 'special_from_date', 'special_to_date'] as $__k) {
                $newRow[$__k] = isset($r[$__k]) && $r[$__k]!=='' ? $r[$__k] : null;
            }
            $newRow['has_error'] = !empty($r['error']);
            $newRow['error_info'] = !empty($r['error']) ? $r['error']
                : (!empty($r['new_assoc']) ? __('Added new vendor/product association') : null);
            $newRow['row_json'] = '';
            $this->_data['rows_log'][] = $newRow;
        } else {
            $this->setNumRows($this->getNumRows()+1);
        }
        return $this;
    }

    public function beforeSave()
    {
        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        if ($this->getAdapter()->getHasOutput()) {
            if ($this->getUseWildcard() && in_array($this->getBatchType(), ['export_orders'])) {
                $this->setPerPoRowsText($this->getAdapter()->getPerPoOutput());
            } else {
                $this->setRowsText($this->getAdapter()->renderOutput());
            }
        }
        if ($this->getRowsLog()) {
            $this->setNumRows($this->getNumRows()+sizeof($this->getRowsLog()));
        }
        if (!$this->getCreatedAt()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        if ($this->getUseWildcard() && in_array($this->getBatchType(), ['export_orders'])) {
            if (is_array($this->getData('per_po_rows_text'))) {
                $this->setData('per_po_rows_text', serialize($this->getData('per_po_rows_text')));
            }
            $this->unsetData('rows_text');
        }
        return parent::beforeSave();
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->getRowsText();
        return $this;
    }

    public function getDistsCollection()
    {
        if (!$this->_distsCollection) {
            $this->_distsCollection = $this->_batchDistFactory->create()->getCollection()
                ->addFieldToFilter('batch_id', $this->getId());
        }
        return $this->_distsCollection;
    }

    public function generateDists($type, $locations=null, $add=false)
    {
        $now = (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT);
        $dists = $this->getDistsCollection();
        if (!$add && $dists->count()) {
            return $this;
        }
        $useWildcard = $this->getUseWildcard();
        if (is_null($locations)) {
            $locations = $this->getVendor()->getData("batch_{$type}_locations");
            $locations = str_replace('[now]', date('YmdHis'), $locations);
        } else {
            $useWildcard = false;
        }
        $locations = array_filter(preg_split("/\r\n|\n\r|\r|\n/", $locations));
        foreach ($locations as $l) {
            if (trim($l)==='') {
                continue;
            }
            if ($this->getUseWildcard()
                && in_array($this->getBatchType(), ['import_orders','import_stockpo','import_inventory'])
                && ($ioAdapter = $this->_modelIo->get(trim($l), $this))
            ) {
                $lsResult = $ioAdapter->filteredLs();
                if (!empty($lsResult)) {
                    foreach ($lsResult as $lsr) {
                        $dist = $this->_batchDistFactory->create()->setBatchId($this->getId())
                            ->setLocation($ioAdapter->createLocationString($lsr['text']))
                            ->setDistStatus('pending')
                            ->setCreatedAt($now);
                        $dists->addItem($dist);
                    }
                }
            } else {
                $dist = $this->_batchDistFactory->create()->setBatchId($this->getId())
                    ->setLocation(trim($l))
                    ->setDistStatus('pending')
                    ->setCreatedAt($now);
                $dists->addItem($dist);
            }
        }
        $dists->save();
        return $this;
    }

    public function retry($cond=null)
    {
        if (is_array($cond)) {
            $this->getDistsCollection()->addFieldToFilter('dist_id', ['in'=>$cond]);
        } elseif ($cond!==false) {
            $this->getDistsCollection()->addFieldToFilter('dist_status', 'error');
        }

        if ($this->getBatchType()=='export_orders') {
            $this->exportOrders();
        } elseif ($this->getBatchType()=='import_orders') {
            $this->importOrders();
        } elseif ($this->getBatchType()=='import_inventory') {
            $this->importInventory();
        } elseif ($this->getBatchType()=='export_stockpo') {
            $this->exportStockpo();
        } elseif ($this->getBatchType()=='import_stockpo') {
            $this->importStockpo();
        }
        return $this;
    }

    public function getPerPoRowsText()
    {
        if ($this->getUseWildcard() && in_array($this->getBatchType(), ['export_orders'])) {
            if (!is_array($this->getData('per_po_rows_text'))) {
                $this->setData('per_po_rows_text', unserialize($this->getData('per_po_rows_text')));
            }
        }
        return $this->getData('per_po_rows_text');
    }

    public function getRowsText()
    {
        if ($this->getUseWildcard() && !$this->getData('rows_text')
            && in_array($this->getBatchType(), ['export_orders','export_stockpo'])
        ) {
            $rows = (array)$this->getPerPoRowsText();
            $rowsText = '';
            if (!empty($rows['header'])) {
                $rowsText .= $rows['header']."\n";
            }
            unset($rows['header']);
            $rowsText .= implode("\n", $rows);
            $this->setData('rows_text', $rowsText);
        } else {
            $rowsText = $this->getData('rows_text');
        }
        return $rowsText;
    }

    public function exportOrders()
    {
        $this->_exportOrders('export_orders');
        return $this;
    }
    public function exportStockpo()
    {
        $this->_exportOrders('export_stockpo');
        return $this;
    }
    protected function _exportOrders($type='export_orders')
    {
        if (!$this->getRowsLog() && !$this->getRowsText()) {
            $this->setBatchStatus('empty')->save();
            return $this;
        }

        $this->setBatchStatus('exporting')->save();

        $this->generateDists($type);

        $defaultEmailSender = $this->_scopeConfig->getValue('udropship/batch/default_email_sender', ScopeInterface::SCOPE_STORE);
        $defaultEmailSubject = $this->_scopeConfig->getValue('udropship/batch/default_email_subject', ScopeInterface::SCOPE_STORE);
        $defaultEmailBody = $this->_scopeConfig->getValue('udropship/batch/default_email_body', ScopeInterface::SCOPE_STORE);
        $defaultExportOrdersFilename = $this->_scopeConfig->getValue("udropship/batch/default_{$type}_filename", ScopeInterface::SCOPE_STORE);

        if ($this->getUseWildcard()) {
            $contentArr = (array)$this->getPerPoRowsText();
            $header = '';
            if (!empty($contentArr['header'])) {
                $header = $contentArr['header'];
            }
            unset($contentArr['header']);
        } else {
            $contentArr = [(string)$this->getRowsText()];
        }

        $success = false;
        $error = false;
        $errorInfo = array();
        foreach ($this->getDistsCollection() as $d) {
            try {
                $d->setDistStatus('exporting')->save();
                $l = $d->getLocation();
                $l = str_replace('{TS}', date('YmdHis'), $l);
                if (preg_match('#^mailto:([^?]+)(.*)$#', $l, $m)) {
                    if ($m[2] && $m[2][0]=='?') {
                        $m[2] = substr($m[2], 1);
                    }
                    parse_str($m[2], $p);
                    $filename = isset($p['filename']) ? $p['filename'] : $defaultExportOrdersFilename;
                    $mFrom = isset($p['from']) ? $p['from'] : $defaultEmailSender;
                    $mSubject = isset($p['subject']) ? $p['subject'] : $defaultEmailSubject;
                    $mBody = isset($p['body']) ? $p['body'] : $defaultEmailBody;
                    if ($filename==='' || $filename==='-') {
                        foreach ($contentArr as $poId => $content) {
                            $content = !empty($header) ? $header."\n".$content : $content;
                            $this->_eventManager->dispatch(
                                "udbatch_{$type}_dist_before",
                                ['batch'=>$this, 'vars'=>['content'=>&$content]]
                            );
                            /** @var \Magento\Framework\Mail\Message $mail */
                            $mail = $this->_hlp->createObj('Magento\Framework\Mail\Message');
                            $mail->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_TEXT)
                                ->setFrom($mFrom)
                                ->addTo($m[1])
                                ->setSubject(str_replace('[po_id]', $poId, $mSubject));
                            if (isset($p['cc'])) {
                                foreach ((array)$p['cc'] as $cc) {
                                    $mail->addCc($cc);
                                }
                            }
                            if (isset($p['bcc'])) {
                                foreach ((array)$p['bcc'] as $cc) {
                                    $mail->addBcc($cc);
                                }
                            }
                            $mail->setBodyText($content);
                            $transport = $this->_hlp->createObj('Magento\Framework\Mail\TransportInterface', ['message' => $mail]);
                            $transport->sendMessage();
                        }
                    } else {

                        /** @var \Magento\Framework\Mail\Message $mail */
                        $mail = $this->_hlp->createObj('Magento\Framework\Mail\Message');
                        $mail->setMessageType(\Magento\Framework\Mail\MessageInterface::TYPE_TEXT)
                            ->setFrom($mFrom)
                            ->addTo($m[1])
                            ->setSubject(str_replace('[po_ids]', implode(',', array_keys($contentArr)), $mSubject));

                        if (isset($p['cc'])) {
                            foreach ((array)$p['cc'] as $cc) {
                                $mail->addCc($cc);
                            }
                        }
                        if (isset($p['bcc'])) {
                            foreach ((array)$p['bcc'] as $cc) {
                                $mail->addBcc($cc);
                            }
                        }
                        $mail->setBodyText(str_replace('[po_ids]', implode(',', array_keys($contentArr)), $mBody));
                        foreach ($contentArr as $poId => $content) {
                            $content = !empty($header) ? $header."\n".$content : $content;
                            $this->_eventManager->dispatch(
                                "udbatch_{$type}_dist_before",
                                ['batch'=>$this, 'vars'=>['content'=>&$content]]
                            );
                            $mail->createAttachment($content, \Zend_Mime::TYPE_TEXT, \Zend_Mime::DISPOSITION_ATTACHMENT, \Zend_Mime::ENCODING_BASE64, $this->generatePoFilename($filename, $poId));
                        }
                        $transport = $this->_hlp->createObj('Magento\Framework\Mail\TransportInterface', ['message' => $mail]);
                        $transport->sendMessage();
                    }
                } else {
                    if (!($ioAdapter = $this->_modelIo->get($l, $this))) {
                        throw new \Exception(__("Unsupported destination '%1'", $l));
                    }
                    foreach ($contentArr as $poId => $content) {
                        $content = !empty($header) ? $header."\n".$content : $content;
                        $this->_eventManager->dispatch(
                            "udbatch_{$type}_dist_before",
                            ['batch'=>$this, 'vars'=>['content'=>&$content]]
                        );
                        $filename = $ioAdapter->getUdbatchGrep() ? $ioAdapter->getUdbatchGrep() : $defaultExportOrdersFilename;
                        $_filename = $this->generatePoFilename($filename, $poId);
                        if (!$ioAdapter->write($_filename, $content)) {
                            $_location = $ioAdapter->createLocationString($_filename, true);
                            throw new \Exception(
                                __("Could not write to file '%1'", $_location)
                            );
                        }
                    }
                }
                $d->setDistStatus('success')->save();
                $success = true;
            } catch (\Exception $e) {
                $d->setDistStatus('error')->setErrorInfo($e->getMessage())->save();
                $error = true;
                $errorInfo[] = $e->getMessage();
            }
        }

        $status = ($success && !$error ? 'success' : (!$success && $error ? 'error' : 'partial'));
        if (!$error) {
            $this->setErrorInfo(null);
        } else {
            $this->setErrorInfo(implode("\n", $errorInfo));
        }
        $this->setBatchStatus($status)->save();

        $this->_eventManager->dispatch("udbatch_{$type}_dist_after", ['batch'=>$this]);

        try {
            $this->exportUpdatePOsStatus();
        } catch (\Exception $e) {
            $this->setErrorInfo("$e")->setBatchStatus('error')->save();
        }

        return $this;
    }

    public function generatePoFilename($filename, $poId)
    {
        $_filename = $filename;
        $changed = false;
        if (strpos($filename, '[po_id]') !== false) {
            $filename = str_replace('[po_id]', $poId, $filename);
            $changed = true;
        }
        if (strpos($filename, '[po_entity_id]') !== false) {
            $filename = str_replace('[po_entity_id]', $this->getPoIncToIdMap($poId), $filename);
            $changed = true;
        }
        if (strpos($filename, '[order_id]') !== false) {
            $filename = str_replace('[order_id]', $this->getPoIncToOrderIdMap($poId), $filename);
            $changed = true;
        }
        if (strpos($filename, '[order_inc_id]') !== false) {
            $filename = str_replace('[order_inc_id]', $this->getPoIncToOrderIncIdMap($poId), $filename);
            $changed = true;
        }
        if (!$changed && $this->getUseWildcard()) {
            if (preg_match('/^(.*)\.([^\.\/]+)$/', $filename, $_m)) {
                $filename = $_m[1].'-'.$poId.'.'.$_m[2];
            } else {
                $filename = $_filename.'-'.$poId;
            }
        }
        return $filename;
    }

    public function getPoIncToIdMap($poIncId)
    {
        return $this->_getPoIncToIdMap($poIncId, 'entity_id');
    }
    public function getPoIncToOrderIdMap($poIncId)
    {
        return $this->_getPoIncToIdMap($poIncId, 'order_id');
    }
    public function getPoIncToOrderIncIdMap($poIncId)
    {
        return $this->_getPoIncToIdMap($poIncId, 'order_inc_id');
    }

    protected function _getPoIncToIdMap($poIncId, $type)
    {
        if ('entity_id' == $type) {
            $mapArr = $this->_incToIdMap;
        } elseif ('order_id' == $type) {
            $mapArr = $this->_incToOrderIdMap;
        } elseif ('order_inc_id' == $type) {
            $mapArr = $this->_incToOrderIncIdMap;
        }
        $mapValue = null;
        if (isset($mapArr[$poIncId])) {
            $mapValue = $mapArr[$poIncId];
        } else {
            $pos = $this->_modelPoFactory->create()->getCollection()
                ->addFieldToFilter('increment_id', ['in'=>[$poIncId]]);
            foreach ($pos as $po) {
                if ($po->getIncrementId()==$poIncId) {
                    $poId = $po->getId();
                    if ('entity_id' == $type) {
                        $mapValue = $po->getId();
                    } elseif ('order_id' == $type) {
                        $mapValue = $po->getOrder()->getId();
                    } elseif ('order_inc_id' == $type) {
                        $mapValue = $po->getOrder()->getIncrementId();
                    }
                    break;
                }
            }
        }
        return $mapValue;
    }

    public function getImportFields()
    {
        return $this->getAdapter()->getImportFields();
    }

    public function getInvImportFields()
    {
        return $this->getAdapter()->getInvImportFields();
    }

    public function isSaveInvImportData()
    {
    	return $this->_scopeConfig->isSetFlag('udropship/batch/save_inventory_import_data', ScopeInterface::SCOPE_STORE);
    }

    public function getPoIncIdFromTrack($track)
    {
        return $this->_getPoIncIdFromTrack($track);
    }
    protected function _getPoIncIdFromTrack($track)
    {
        if ($this->_hlp->isUdpoActive()) {
            if (($po = $this->_hlp->udpoHlp()->getShipmentPo($track->getShipment()))) {
                return $po->getIncrementId();
            }
            return false;
        }
        return $track->getShipment()->getIncrementId();
    }

    public function getMarkAsShippedStatuses()
    {
        return $this->_getMarkAsShippedStatuses();
    }
    protected function _getMarkAsShippedStatuses()
    {
        if ($this->_hlp->isUdpoActive()) {
            return [\Unirgy\DropshipPo\Model\Source::UDPO_STATUS_SHIPPED, \Unirgy\DropshipPo\Model\Source::UDPO_STATUS_DELIVERED];
        } else {
            return [\Unirgy\Dropship\Model\Source::SHIPMENT_STATUS_SHIPPED, \Unirgy\Dropship\Model\Source::SHIPMENT_STATUS_DELIVERED];
        }
    }
    public function isStatusDelivered($status)
    {
        if ($this->_hlp->isUdpoActive()) {
            return $status == \Unirgy\DropshipPo\Model\Source::UDPO_STATUS_DELIVERED;
        } else {
            return $status == \Unirgy\Dropship\Model\Source::SHIPMENT_STATUS_DELIVERED;
        }
    }

    public function processTrack($po, $track, $markAsShipped)
    {
        return $this->_processTrack($po, $track, $markAsShipped);
    }
    protected function _processTrack($po, $track, $markAsShipped)
    {
        $shipment = $po;
        if (is_a($po, 'Unirgy\DropshipPo\Model\Po')) {
            $_shipment = false;
            foreach ($po->getShipmentsCollection() as $_s) {
                if ($_s->getUdropshipStatus()==\Unirgy\Dropship\Model\Source::SHIPMENT_STATUS_CANCELED) {
                    continue;
                }
                $_shipment = $_s;
            }
            if (!$_shipment) {
                $shipment = $this->_hlp->udpoHlp()->createShipmentFromPo($po);
            } else {
                $shipment = $_shipment;
            }
        }
        if (empty($shipment)) throw new \Exception('cannot find/initialize shipment record');
        $shipment->addTrack($track);
        if ($track->getData('__update_date')) {
            $shipment->setCreatedAt($track->getCreatedAt());
        }
        $this->_hlp->addShipmentComment(
            $shipment,
            __('Tracking ID %1 was added', $track->getNumber())
        );
        $this->_hlp->processTrackStatus($track, true, $markAsShipped);
        $shipment->setData('__dummy',1)->save();
    }

    public function importOrders()
    {
        $this->_importOrders('import_orders');
        return $this;
    }
    public function importStockpo()
    {
        $this->_importOrders('import_stockpo');
        return $this;
    }
    protected  function _importOrders($type='import_orders')
    {
        $this->setBatchStatus('importing')->save();

        $this->generateDists($type);

        $resourceModel = $this->_getResource();

        $success = false;
        $error = false;
        $empty = true;
        $errorInfo = array();
        foreach ($this->getDistsCollection() as $d) {
            $oldRowsText = $this->getRowsText();
            try {
                $d->setDistStatus('importing')->save();
                $l = $d->getLocation();
                if ($io = $this->_modelIo->get($l, $this)) {
                    $text = $io->read($io->getUdbatchGrep());
                } else {
                    $text = @file_get_contents($l);
                }
                if ($text===false || is_null($text)) {
                    throw new \Exception(__("Could not read from file '%1'", $l));
                }
                if ($text=='') {
                    $d->setDistStatus('empty')->save();
                    continue;
                }

                $this->_eventManager->dispatch("udbatch_{$type}_dist_after", ['batch'=>$this, 'dist'=>$d, 'vars'=>['content'=>&$text]]);

                $this->getAdapter()->import($text);

                if ($io) $this->_performFileAction($io, "batch_{$type}");

                $this->setRowsText((!empty($oldRowsText) ? $oldRowsText."\n" : $oldRowsText) . $text)->save();

                $d->setDistStatus('success')->setErrorInfo(null)->save();
                $success = true;
                $empty = false;
            } catch (\Exception $e) {
                $errorInfo[] = $e->getMessage();
                $this->setRowsText($oldRowsText);
                $d->setDistStatus('error')->setErrorInfo($e->getMessage())->save();
                $error = true;
                $empty = false;
            }
        }

        $status = $empty ? 'empty' : ($success && !$error ? 'success' : (!$success && $error ? 'error' : 'partial'));
        if (!$error) {
            $this->setErrorInfo(null);
        } else {
            $this->setErrorInfo(implode("\n", $errorInfo));
        }
        $this->setBatchStatus($status)->save();

        $this->importUpdatePOsStatus();

        $this->importUpdateVendorTs();

        return $this;
    }

	public function importInventory()
    {
        $this->setBatchStatus('importing')->save();

        $this->generateDists('import_inventory');

        $resourceModel = $this->_getResource();

        $success = false;
        $error = false;
        $empty = true;
        $errorInfo = array();
        foreach ($this->getDistsCollection() as $d) {
            $oldRowsText = $this->getRowsText();
            try {
                $d->setDistStatus('importing')->save();
                $l = $d->getLocation();
                if ($io = $this->_modelIo->get($l, $this)) {
                    $text = $io->read($io->getUdbatchGrep());
                } else {
                    $text = @file_get_contents($l);
                }
                if ($text===false || is_null($text)) {
                    throw new \Exception(__("Could not read from file '%1'", $l));
                }
                if ($text=='') {
                    $d->setDistStatus('empty')->save();
                    continue;
                }

                $this->_eventManager->dispatch('udbatch_import_inventory_dist_after', ['batch'=>$this, 'dist'=>$d, 'vars'=>['content'=>&$text]]);

	            $this->getAdapter()->import($text);

                if ($io) $this->_performFileAction($io, 'batch_import_inventory');

                $d->setDistStatus('success')->setErrorInfo(null)->save();

                if ($this->isSaveInvImportData()) {
	        		$this->setRowsText((!empty($oldRowsText) ? $oldRowsText."\n" : $oldRowsText) . $text);
	        	} else {
                    $this->unsRowsText();
	        	}
                $this->save();

                $success = true;
                $empty = false;
            } catch (\Exception $e) {
                $errorInfo[] = $e->getMessage();
                $this->setRowsText($oldRowsText);
                $d->setDistStatus('error')->setErrorInfo($e->getMessage())->save();
                $error = true;
                $empty = false;
            }
        }

        $status = $empty ? 'empty' : ($success && !$error ? 'success' : (!$success && $error ? 'error' : 'partial'));
        if (!$error) {
            $this->setErrorInfo(null);
        } else {
            $this->setErrorInfo(implode("\n", $errorInfo));
        }
        $this->setBatchStatus($status)->save();

        $this->importUpdateVendorTs();

        return $this;
    }

    protected function _performFileAction($io, $prefix)
    {
        if ($this->getSkipFileactionsFlag()) return $this;
        $v = $this->getVendor();
        $action = $v->getData($prefix.'_file_action');
        if ($action == 'delete') {
            return $io->rm($io->getUdbatchGrep());
        }
        $destFile = $io->getUdbatchGrep();
        if (in_array($action, ['rename','rename_move'])) {
            $destFile = implode('', [
                trim($v->getData($prefix.'_rename_prefix')),
                $destFile,
                trim($v->getData($prefix.'_rename_suffix'))
            ]);
        }
        if (in_array($action, ['move','rename_move'])) {
            $destFile = $v->getData($prefix.'_move_folder').'/'.ltrim($destFile, '/\\');
        }
        if ($io->getUdbatchGrep() != $destFile && in_array($action, ['rename','move','rename_move'])) {
            return $io->mv($io->getUdbatchGrep(), $destFile);
        }
    }

    public function importUpdateVendorTs()
    {
        if ($this->getSkipFileactionsFlag()) return $this;
        $vendor = $this->getVendor();
        if ($this->getBatchType()=='import_orders') {
            if ($vendor->getData('batch_import_orders_track_dist_ts')
                && $vendor->getData('batch_import_orders_ts_newts')
            ) {
                $vendor->updateData([
                     'batch_import_orders_ts' => $vendor->getData('batch_import_orders_ts_newts')
                ]);
                $vendor->unsetData('batch_import_orders_ts_newts');
            }
        } elseif ($this->getBatchType()=='import_stockpo') {
            if ($vendor->getData('batch_import_stockpo_track_dist_ts')
                && $vendor->getData('batch_import_stockpo_ts_newts')
            ) {
                $vendor->updateData([
                     'batch_import_stockpo_ts' => $vendor->getData('batch_import_stockpo_ts_newts')
                ]);
                $vendor->unsetData('batch_import_stockpo_ts_newts');
            }
        } elseif ($this->getBatchType()=='import_inventory') {
            if ($vendor->getData('batch_import_inventory_track_dist_ts')
                && $vendor->getData('batch_import_inventory_ts_newts')
            ) {
                $vendor->updateData([
                     'batch_import_inventory_ts' => $vendor->getData('batch_import_inventory_ts_newts')
                ]);
                $vendor->unsetData('batch_import_inventory_ts_newts');
            }
        }
        return $this;
    }

    public function importUpdatePOsStatus()
    {
        $res = $this->_hlp->rHlp();
        $w = $res->getConnection();

        $poIds = $w->fetchCol("select po_id from {$res->getTableName('udropship_batch_row')} where batch_id={$this->getId()} and has_error=0");
        if (!$poIds) {
            return $this;
        }

        $pos = $this->_modelPoFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$poIds]);
        if (!$pos->count()) {
            return $this;
        }

        $status = $this->getVendor()->getData("batch_{$this->getBatchType()}_po_status");

        $stockPos = [];
        foreach ($pos as $po) {
            $po->setForceStatusChangeFlag(true);
            $this->_hlp->processPoStatusSave($po, $status);
            if ($this->getBatchType() == 'import_stockpo' && $po->getStockPo() && $po->getStockPo()->getId()) {
                $stockPos[$po->getStockPo()->getId()] = $po->getStockPo();
            }
        }
        if ($this->getBatchType() == 'import_stockpo') {
            $stockPoStatus = $this->getVendor()->getData("batch_{$this->getBatchType()}_stockpo_status");
            foreach ($stockPos as $stockPo) {
                $this->_hlp->processPoStatusSave($stockPo, $stockPoStatus);
            }
        }

        return $this;
    }

    public function exportUpdatePOsStatus()
    {
        $res = $this->_hlp->rHlp();
        $w = $res->getConnection('sales_write');
        $poIds = $w->fetchCol("select po_id from {$res->getTableName('udropship_batch_row')} where batch_id='{$this->getId()}'");
        if (!$poIds) {
            return $this;
        }
        $pos = $this->_modelPoFactory->create()->getCollection()
            ->addFieldToFilter('entity_id', ['in'=>$poIds]);
        if ($this->getBatchType() == 'export_stockpo') {
            $pos->addPendingStockpoBatchStatusFilter();
        } else {
            $pos->addPendingBatchStatusVendorFilter($this->getVendor());
        }
        $pos->addOrders();
        if ($this->getBatchType() == 'export_stockpo') {
            $pos->addStockPos();
        }
        if (!$pos->count()) {
            return $this;
        }

        $status = $this->getVendor()->getData("batch_{$this->getBatchType()}_po_status");
        $markAsShipped = in_array($status, $this->_getMarkAsShippedStatuses());
        $stockPos = [];
        foreach ($pos as $po) {
            $shipment = $po;
            if ($this->_hlp->isUdpoActive()) {
                if ($markAsShipped) {
                    if ($po->getShipmentsCollection()->count()>0) {
                        $shipment = $po->getShipmentsCollection()->getLastItem();
                    } else {
                        $shipment = $this->_hlp->udpoHlp()->createShipmentFromPo($po);
                    }
                    $this->_hlp->completeShipment($shipment, true, $this->isStatusDelivered($status))
                        ->completeOrderIfShipped($shipment, true);
                }
            } else {
                if ($markAsShipped) {
                    $this->_hlp->completeShipment($po, true, $this->isStatusDelivered($status))
                        ->completeOrderIfShipped($po, true);
                }
            }
            if ($this->getBatchType()=='export_orders') {
                $po->setUdropshipBatchStatus('exported');
                $po->getResource()->saveAttribute($po, 'udropship_batch_status');
            }
            $po->setForceStatusChangeFlag(true);
            $this->_hlp->processPoStatusSave($po, $status);
            if ($this->getBatchType() == 'export_stockpo' && $po->getStockPo() && $po->getStockPo()->getId()) {
                $stockPos[$po->getStockPo()->getId()] = $po->getStockPo();
            }
        }

        if ($this->getBatchType() == 'export_stockpo') {
            $stockPoStatus = $this->getVendor()->getData("batch_{$this->getBatchType()}_stockpo_status");
            foreach ($stockPos as $stockPo) {
                $this->_hlp->processPoStatusSave($stockPo, $stockPoStatus);
            }
        }

        return $this;
    }

    public function getUseWildcard()
    {
        if (null == $this->getData('use_wildcard')
            && $this->getVendor()->getId()
        ) {
            switch ($this->getBatchType()) {
                case 'export_orders':
                    $this->setData('use_wildcard', $this->getVendor()->getData('batch_export_orders_use_wildcard'));
                    break;
                case 'import_orders':
                    $this->setData('use_wildcard', $this->getVendor()->getData('batch_import_orders_use_wildcard'));
                    break;
                case 'import_inventory':
                    $this->setData('use_wildcard', $this->getVendor()->getData('batch_import_inventory_use_wildcard'));
                    break;
                case 'export_stockpo':
                    $this->setData('use_wildcard', $this->getVendor()->getData('batch_export_stockpo_use_wildcard'));
                    break;
                case 'import_stockpo':
                    $this->setData('use_wildcard', $this->getVendor()->getData('batch_import_stockpo_use_wildcard'));
                    break;
            }
        }
        return $this->getData('use_wildcard');
    }

    public function getLocationPathIsDirectory()
    {
        return false;
    }
}