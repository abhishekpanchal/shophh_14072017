<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ImportOrders;

use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Shipping\Model\Config;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\PoFactory;
use Unirgy\Dropship\Model\Source;

class DefaultImportOrders extends AbstractImportOrders
{
    /**
     * @var HelperData
     */
    protected $_bHlp;

    /**
     * @var OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var PoFactory
     */
    protected $_poFactory;

    /**
     * @var TrackFactory
     */
    protected $_shipmentTrackFactory;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Config
     */
    protected $_shippingConfig;

    public function __construct(
        HelperData $batchHelper,
        OrderFactory $modelOrderFactory, 
        PoFactory $modelPoFactory, 
        TrackFactory $shipmentTrackFactory, 
        DropshipHelperData $dropshipHelper,
        ManagerInterface $eventManager,
        Config $shippingConfig,
        array $data = []
    )
    {
        $this->_bHlp = $batchHelper;
        $this->_orderFactory = $modelOrderFactory;
        $this->_poFactory = $modelPoFactory;
        $this->_shipmentTrackFactory = $shipmentTrackFactory;
        $this->_hlp = $dropshipHelper;
        $this->_eventManager = $eventManager;
        $this->_shippingConfig = $shippingConfig;

        parent::__construct($data);
    }

    public function init()
    {}
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

        $fields = $this->getImportFields();
        $rows = [];
        $idx = 0;
        while (!feof($fp)) {
            $r = @fgetcsv($fp, 0, $this->getVendor()->getBatchImportOrdersFieldDelimiter(), '"');
            if (!$idx++ && $this->getVendor()->getBatchImportOrdersSkipHeader()) continue;
            if (!$r) {
                $rows[] = ['error'=>__('Invalid row format')];
                continue;
            }
            $row = [];
            foreach ($r as $i=>$v) {
                if (isset($fields[$i])) {
                    $row[$fields[$i]] = trim($v);
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
            $tpl = $this->getVendor()->getBatchImportOrdersTemplate();
            if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())
                && ($_custTpl = $this->_bHlp->getManualImportTemplate($_useCustTpl))
            ) {
                $tpl = $_custTpl;
            }
            $this->setData('import_template', $tpl);
            $this->getBatch()->setData('import_template', $tpl);
            if (!preg_match_all('#\[([^]]+)\]([^[]+)?#', $tpl, $m, PREG_PATTERN_ORDER)) {
                throw new \Exception('Invalid import template');
            }
            if (!in_array('po_id', $m[1]) && !in_array('order_id', $m[1]) || !in_array('tracking_id', $m[1])) {
                throw new \Exception('Missing required field');
            }
            if (in_array('po_id', $m[1]) && in_array('order_id', $m[1])) {
                throw new \Exception('Either po_id OR order_id can be specified, but not both');
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
            if (empty($r['po_id']) && empty($r['order_id']) || empty($r['tracking_id'])) {
                $r['error'] = __('Missing required field');
                continue;
            }
            if (!empty($trackIds[$r['tracking_id']])) {
                $r['error'] = __('Duplicate tracking_id within file');
                continue;
            }
            if (!empty($r['po_id'])) {
                $poIds[$r['po_id']] = $i;
            } else {
                $orderIds[$r['order_id']] = $i;
            }
            $trackIds[$r['tracking_id']] = $i;
        }
        unset($r);

        if ($orderIds) {
            $orders = $this->_orderFactory->create()->getCollection()
                ->addFieldToFilter('increment_id', ['in'=>array_keys($orderIds)]);
            if ($orders->count()) {
                // @see http://groups.google.com/group/magento-devel/browse_thread/thread/d7afe64c5da94b27
                #$oIds = $orders->getAllIds();
                $oIds2 = $oIds = [];
                foreach ($orders as $o) {
                    $oIds[$o->getId()] = $o->getIncrementId();
                    $oIds2[$o->getId()] = (int)$o->getIncrementId();
                }
                $pos = $this->_poFactory->create()->getCollection()
                    ->addFieldToFilter('order_id', ['in'=>array_keys($oIds)])
                    ->addFieldToFilter('udropship_vendor', $this->getVendorId());
                foreach ($pos as $po) {
                    if (array_key_exists($po->getOrderId(), $oIds)
                        && array_key_exists($oIds[$po->getOrderId()], $orderIds)
                    ) {
                        $poIds[$po->getIncrementId()] = $orderIds[$oIds[$po->getOrderId()]];
                    } elseif (array_key_exists($po->getOrderId(), $oIds2)
                        && array_key_exists($oIds2[$po->getOrderId()], $orderIds)
                    ) {
                        $poIds[$po->getIncrementId()] = $orderIds[$oIds2[$po->getOrderId()]];
                    }
                }
            }
        }

        foreach ($rows as $i=>&$r) {
            if (!in_array($i, $poIds)) {
                $r['error'] = __('Invalid Order or PO ID');
            }
            if (empty($r['po_id'])) {
                $r['po_id'] = array_search($i, $poIds);
            }
        }
        unset($r);

        // find already existing tracking_ids
        $tracks = $this->_shipmentTrackFactory->create()->getCollection()
            ->addAttributeToFilter($this->_hlp->trackNumberField(), ['in'=>array_keys($trackIds)]);
        foreach ($tracks as $t) {
            $i = $trackIds[$t->getNumber()];
            $poId = $this->getBatch()->getPoIncIdFromTrack($t);
            $r =& $rows[$i];
            if (!$allowDupTrackIds || isset($r['po_id']) && $r['po_id']==$poId) {
                $r['error'] = __('Duplicate tracking_id');
                $r['track_id'] = $t->getId();
                #unset($poIds[$poId]);
            }
        }
        unset($r);

        return $poIds;
    }

    public function process($rows)
    {
        $markAsShipped = in_array(
            $this->getVendor()->getData('batch_import_orders_po_status'),
            $this->getBatch()->getMarkAsShippedStatuses()
        );

        $hlp = $this->_bHlp;
        $allowDupTrackIds = false;

        $this->_eventManager->dispatch(
            'udbatch_import_orders_convert_before',
            ['batch'=>$this->getBatch(), 'adapter'=>$this, 'vars'=>['rows'=>&$rows]]
        );

        $poIds = $this->_validateRows($rows);

        // find POs and orders
        $pos = $this->_poFactory->create()->getCollection()
            ->addAttributeToFilter('increment_id', ['in'=>array_keys($poIds)]);
        if (!$this->getBatch()->getIsAllVendorsImport()) {
            $pos->addAttributeToFilter('udropship_vendor', $this->getVendorId());
        }
        $pos->addOrders();

        $carriers = $this->getCarriers();
        foreach ($pos as $po) {
            $order = $po->getOrder();

            try {
                $__poIdKey = $po->getIncrementId();
                $__poIdKey2 = (int)$po->getIncrementId();
                if (array_key_exists($__poIdKey, $poIds)) {
                    $r = $rows[$poIds[$__poIdKey]];
                } elseif (array_key_exists($__poIdKey2, $poIds)) {
                    $r = $rows[$poIds[$__poIdKey2]];
                }
                if (empty($r['error'])) {
                    $method = explode('_', $po->getUdropshipMethod(), 2);
                    $carrier = !empty($r['carrier']) ? $r['carrier'] : 'custom';
                    $title = !empty($r['title']) ? $r['title'] : (isset($carriers[$carrier]) ? $carriers[$carrier] : $method[0]);
                    $track = $this->_shipmentTrackFactory->create()
                        ->setTrackNumber($r['tracking_id'])
                        ->setCarrierCode($carrier)
                        ->setTitle($title)
                        ->setUdropshipStatus(Source::TRACK_STATUS_READY);
                    if (isset($r['shipping_date']) && !empty($r['shipping_date'])) {
                        $track->setCreatedAt($r['shipping_date']);
                        $track->setData('__update_date', 1);
                    }
                    $this->getBatch()->processTrack($po, $track, $markAsShipped);
                }
            } catch (\Exception $e) {
                $r['error'] = __($e->getMessage());
            }
            $this->getBatch()->addImportRowLog($order, $po, $r, @$track);
        }

        return $this;
    }

    protected $_carriers;
    public function getCarriers()
    {
        if ($this->_carriers===null) {
            $carriers = [];
            $carrierInstances = $this->_shippingConfig->getAllCarriers();
            $carriers['custom'] = __('Custom Value');
            foreach ($carrierInstances as $code => $carrier) {
                if ($carrier->isTrackingAvailable()) {
                    $carriers[$code] = $carrier->getConfigData('title');
                }
            }
            $this->_carriers = $carriers;
        }
        return $this->_carriers;
    }

}
