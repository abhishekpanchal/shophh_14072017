<?php

namespace Unirgy\DropshipBatch\Model\Adapter;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\TemplateFilterFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\Item;

abstract class AbstractAdapter extends DataObject
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
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var Item
     */
    protected $_iHlp;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var Message
     */
    protected $_helperMessage;

    /**
     * @var ItemFactory
     */
    protected $_shipmentItemFactory;

    /**
     * @var OrderItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @var TemplateFilterFactory
     */
    protected $_templateFilterFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        HelperData $batchHelper,
        OrderFactory $orderFactory,
        DropshipHelperData $dropshipHelper,
        Item $helperItem, 
        ManagerInterface $eventManager,
        Message $helperMessage, 
        ItemFactory $shipmentItemFactory, 
        OrderItemFactory $orderItemFactory,
        TemplateFilterFactory $templateFilterFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    )
    {
        $this->_bHlp = $batchHelper;
        $this->_orderFactory = $orderFactory;
        $this->_hlp = $dropshipHelper;
        $this->_iHlp = $helperItem;
        $this->_eventManager = $eventManager;
        $this->_helperMessage = $helperMessage;
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_templateFilterFactory = $templateFilterFactory;
        $this->_scopeConfig = $scopeConfig;

        parent::__construct($data);
    }

    public function init()
    {
        $this->setHasOutput(false);
        return $this;
    }

    public function getBatchExportOrdersTemplate()
    {
        $tpl = $this->getVendor()->getBatchExportOrdersTemplate();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $tpl = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'template');
        }
        return $tpl;
    }
    public function getBatchExportOrdersTotalsTemplate()
    {
        $tpl = $this->getVendor()->getBatchExportOrdersTotalsTemplate();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $tpl = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'totals_template');
        }
        return $tpl;
    }
    public function getUseItemExportTemplate()
    {
        $flag = $this->getVendor()->getBatchExportOrdersUseItemTemplate();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $flag = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'use_item_template');
        }
        return $flag;
    }
    public function getBatchExportOrdersItemTemplate()
    {
        $tpl = $this->getVendor()->getBatchExportOrdersItemTemplate();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $tpl = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'item_template');
        }
        return $tpl;
    }
    public function getBatchExportOrdersItemFooterTemplate()
    {
        $tpl = $this->getVendor()->getBatchExportOrdersItemFooterTemplate();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $tpl = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'footer_template');
        }
        return $tpl;
    }
    public function getBatchExportOrdersHeader()
    {
        $tpl = $this->getVendor()->getBatchExportOrdersHeader();
        if (($_useCustTpl = $this->getBatch()->getUseCustomTemplate())) {
            $tpl = $this->_bHlp->getManualExportTemplate($_useCustTpl, 'header');
        }
        return $tpl;
    }
    public function getExportTemplate()
    {
        if (!$this->hasData('export_template')) {
            $exportTpl = $this->getBatchExportOrdersTemplate();
            if (trim($exportTpl) == '') {
                throw new \Exception(__('Empty Export Template'));
            }
            $this->setData('export_template', $exportTpl);
        }
        return $this->getData('export_template');
    }
    public function getItemExportTemplate()
    {
        if (!$this->getUseItemExportTemplate()) return '';
        if (!$this->hasData('item_export_template')) {
            $exportTpl = $this->getBatchExportOrdersItemTemplate();
            if (trim($exportTpl) == '') {
                throw new \Exception(__('Empty Item Export Template'));
            }
            $this->setData('item_export_template', $exportTpl);
        }
        return $this->getData('item_export_template');
    }
    public function getItemFooterExportTemplate()
    {
        //if (!$this->getUseItemExportTemplate()) return '';
        if (!$this->hasData('item_footer_export_template')) {
            $exportTpl = $this->getBatchExportOrdersItemFooterTemplate();
            $this->setData('item_footer_export_template', $exportTpl);
        }
        return $this->getData('item_footer_export_template');
    }

    public function getSkipItemStatuses()
    {
        return $this->getBatch()->getSkipItemStatuses();
    }
    public function getVendorId()
    {
        return $this->getVendor()->getId();
    }
    public function getVendor()
    {
        return $this->getBatch()->getVendor();
    }

    abstract public function addPO($po);

    abstract public function renderOutput();

    public function preparePO($po)
    {
        $order = $po->getOrder();
        if (!$order->getEntityId()) {
            $order = $this->_orderFactory->create()->load($po->getOrderId() ? $po->getOrderId() : $po->getParentId());
        }
        $this->processGiftMessage($order);
        $uvendorGiftmessage = $order->getData('uvendor_giftmessage');
        if (is_string($uvendorGiftmessage)) {
            $uvendorGiftmessage = @unserialize($uvendorGiftmessage);
        }
        $vId = $this->getVendorId();
        if (!empty($uvendorGiftmessage[$vId])) {
            $this->processGiftMessage($po, $uvendorGiftmessage[$vId]);
        }
        $this->setOrder($order);
        $this->setPo($po);
        $this->_hlp->addVendorSkus($po);
        $this->_iHlp->initPoTotals($po);

        $billing = $this->getOrder()->getBillingAddress();
        $shipping = $this->getOrder()->getShippingAddress();

        if (!$shipping) {
            $shipping = $billing;
        }

        if (!$billing->getEmail()) {
            $billing->setEmail($order->getCustomerEmail());
        }
        if (!$shipping->getEmail()) {
            $shipping->setEmail($order->getCustomerEmail());
        }

        $udMethod = $this->_hlp->mapSystemToUdropshipMethod(
            $this->getPo()->getUdropshipMethod(),
            $this->getVendor()
        );

        $this->setUdropshipMethod($udMethod);

        $udMethodArr = explode('_', $this->getPo()->getUdropshipMethod(), 2);
        $cMethodNames = !empty($udMethodArr[0])
            ? $this->_hlp->getCarrierMethods(@$udMethodArr[0])
            : [];
        $this->setSystemCarrierTitle(
            !empty($udMethodArr[0]) ? $this->_hlp->getCarrierTitle(@$udMethodArr[0]) : ''
        );
        $this->setSystemMethodTitle(@$cMethodNames[@$udMethodArr[1]]);
        $this->setSystemCarrierCode(@$udMethodArr[0]);
        $this->setSystemMethodCode(@$udMethodArr[1]);

        if ($this->getPo() instanceof \Unirgy\DropshipPo\Model\Po) {
            $poStatus = $this->_hlp->udpoHlp()->getPoStatusName($this->getPo()->getUdropshipStatus());
        } else {
            $poStatus = $this->_hlp->getShipmentStatusName($this->getPo()->getUdropshipStatus());
        }
        
        $vars = [
            'vendor'=>$this->getVendor(),
            'system_carrier_title' => $this->getSystemCarrierTitle(),
            'system_carrier_code' => $this->getSystemCarrierCode(),
            'system_method_title' => $this->getSystemMethodTitle(),
            'system_method_code' => $this->getSystemMethodCode(),
            'udropship_method' => $this->getUdropshipMethod(),
            'udropship_method_code' => $this->getUdropshipMethod()->getShippingCode(),
            'udropship_method_title' => $this->getUdropshipMethod()->getShippingTitle(),
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $billing,
            'shipping' => $shipping,
            'po' => $this->getPo(),
            'po_status' => $poStatus,
            'po_id' => $this->getPo()->getIncrementId(),
            'po_totals' => $this->getPo()->getUdropshipTotalAmounts()
        ];
        $this->_eventManager->dispatch('udbatch_prepare_po', ['vars'=>&$vars]);
        $this->setVars($vars);

        return true;
    }

    public function processGiftMessage($object, $gmId=null)
    {
        $gmHlp = $this->_helperMessage;
        if (null === $gmId) {
            $gmId = $object->getGiftMessageId();
        }
        if ($gmId && ($_giftMessage = $gmHlp->getGiftMessage($gmId))
        ) {
            $object->setGiftMessageFrom($_giftMessage->getSender());
            $object->setGiftMessageFromWithLabel(__('From:').' '.$_giftMessage->getSender());
            $object->setGiftMessageTo($_giftMessage->getRecipient());
            $object->setGiftMessageToWithLabel(__('To:').' '.$_giftMessage->getRecipient());
            $object->setGiftMessageText($_giftMessage->getMessage());
            $object->setGiftMessageTextWithLabel(__('Message:').' '.$_giftMessage->getMessage());
            $object->setGiftMessageCombined(
                __('From:').' '.$_giftMessage->getSender()."\n".
                __('To:').' '.$_giftMessage->getRecipient()."\n".
                __('Message:').' '.$_giftMessage->getMessage()
            );
        }
    }

    public function preparePOItem($item)
    {
        if (!$this->getOrder()) {
            throw new \Exception('Order is not initialized');
        }

        $orderItem = $item->getOrderItem();
        if (!$orderItem) {
            if (!$item->getOrderItemId()) {
                $item = $this->_shipmentItemFactory->create()->load($item->getEntityId());
            }
            $orderItem = $this->_orderItemFactory->create()->load($item->getOrderItemId());
        }

        $children = [];
        if ($orderItem->getChildren()) {
            $children = $orderItem->getChildren();
        } elseif ($orderItem->getChildrenItems()) {
            $children = $orderItem->getChildrenItems();
        }

        if (!empty($children) && $this->getPo() instanceof DataObject && $this->getPo()->getAllItems()) {
            foreach ($children as $oiChild) {
                foreach ($this->getPo()->getAllItems() as $piChild) {
                    if ($piChild->getOrderItem()===$oiChild) {
                        $piChild->setParentPoItem($item);
                        $piChild->setParentPoItemId($item->getId());
                        break;
                    }
                }
            }
        }

        if ($item->getParentPoItem() && $orderItem->isDummy(true) && $orderItem->getParentItem()) {
            $this->setOrigQty($item->getQty());
            $item->getParentPoItem()->getQty()*$orderItem->getQtyOrdered()/max(1, $orderItem->getParentItem()->getQtyOrdered());
            $item->setData('qty', $item->getParentPoItem()->getQty()*$orderItem->getQtyOrdered()/max(1, $orderItem->getParentItem()->getQtyOrdered()));
        }

        if (($orderItem->getChildren() || $orderItem->getChildrenItems() and $orderItem->getProductType() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            || ($orderItem->getParentItem() and $orderItem->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            || in_array($orderItem->getStatusId(), $this->getSkipItemStatuses())) {
            return false;
        }

        $this->setOrderItem($orderItem);
        $this->setPoItem($item);

        $productOptions = $this->getOrderItem()->getProductOptions();
        $productOptionsArr = [];
        if (!empty($productOptions['options'])) {
            foreach ($productOptions['options'] as $o) {
                $productOptionsArr[] = $o['label'].': '.(!empty($o['print_value']) ? $o['print_value'] : $o['value']);
            }
        }
        if (!empty($productOptions['attributes_info'])) {
            foreach ($productOptions['attributes_info'] as $o) {
                $productOptionsArr[] = $o['label'].': '.$o['value'];
                if (strtolower($o['label'])=='size') {
                    $item->setProductOptionSize($o['value']);
                }
                if (strtolower($o['label'])=='color') {
                    $item->setProductOptionColor($o['value']);
                }
            }
        }
        if (!empty($productOptions['additional_options'])) {
            foreach ($productOptions['additional_options'] as $o) {
                $productOptionsArr[] = $o['label'].': '.(!empty($o['print_value']) ? $o['print_value'] : $o['value']);
            }
        }
        if ($orderItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $this->setOrigSku($item->getSku());
            $item->setSku($orderItem->getProductOptionByCode('simple_sku'));
            if ($item->getVendorSimpleSku()) {
                $this->setOrigVendorSku($item->getVendorSku());
                $item->setVendorSku($item->getVendorSimpleSku());
            }
        }
        $this->processGiftMessage($this->getOrderItem());

        if ($this->getPo() instanceof \Unirgy\DropshipPo\Model\Po) {
            $poStatus = $this->_hlp->udpoHlp()->getPoStatusName($this->getPo()->getUdropshipStatus());
        } else {
            $poStatus = $this->_hlp->getShipmentStatusName($this->getPo()->getUdropshipStatus());
        }

        $vars = [
            'vendor'=>$this->getVendor(),
            'system_carrier_title' => $this->getSystemCarrierTitle(),
            'system_carrier_code' => $this->getSystemCarrierCode(),
            'system_method_title' => $this->getSystemMethodTitle(),
            'system_method_code' => $this->getSystemMethodCode(),
            'udropship_method' => $this->getUdropshipMethod(),
            'udropship_method_code' => $this->getUdropshipMethod()->getShippingCode(),
            'udropship_method_title' => $this->getUdropshipMethod()->getShippingTitle(),
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $this->getOrder()->getBillingAddress(),
            'shipping' => $this->getOrder()->getShippingAddress(),
            'po' => $this->getPo(),
            'po_id' => $this->getPo()->getIncrementId(),
            'po_totals' => [],
            'po_status' => $poStatus,
            'item' => $this->getPoItem(),
            'item_totals' => $this->getPoItem()->getUdropshipTotalAmounts(),
            'order_item' => $this->getOrderItem(),
            'product_options' => join('; ', $productOptionsArr),
            'product' => $this->getPoItem()->getProductForBatch(),
        ];
        if ($this->getPo()->getUdbatchTotalLines()==$this->getPoItem()->getUdbatchLineNumber()) {
            $vars['po_totals'] = $this->getPo()->getUdropshipTotalAmounts();
        }
        $this->_eventManager->dispatch('udbatch_prepare_po_item', ['vars'=>&$vars]);
        $this->setVars($vars);

        return true;
    }

    public function restoreItem()
    {
        if ($this->getOrigSku()) {
            $this->getPoItem()->setSku($this->getOrigSku());
            $this->unsOrigSku();
        }
        if ($this->hasOrigVendorSku()) {
            $this->getPoItem()->setVendorSku($this->getOrigVendorSku());
            $this->unsOrigVendorSku();
        }
        if ($this->getOrigQty()) {
            $this->getPoItem()->setData('qty', $this->getOrigQty());
            $this->unsOrigQty();
        }
        return $this;
    }

    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = $this->_templateFilterFactory->create();
        }
        return $this->_templateFilter;
    }

    public function renderTemplate($text, $vars)
    {
        if (preg_match_all('#\[([a-z0-9._]+(?::[^\]]*)?)\]#i', $text, $m, PREG_PATTERN_ORDER)) {
            $keys = array_unique($m[1]);
            $replaceFrom = [];
            $replaceTo = [];
            foreach ($keys as $key) {
                $_key = explode(':', $key, 2);
                $keyArr = explode('.', $_key[0]);
                $value = $vars;
                foreach ($keyArr as $k) {
                    if (!isset($value[$k])) {
                        $value = '';
                        break;
                    }
                    $value = $value[$k];
                }

                $replaceFrom[] = '['.$key.']';
                $value = empty($value) && !empty($_key[1]) ? $_key[1] : $value;
                $value = is_numeric($value) ? $value*1 : $value;
                $value = str_replace('"', '""', $value);
                $replaceTo[] = is_numeric($value) ? $value*1 : $value;
            }
            if ($this->_scopeConfig->isSetFlag('udropship/batch/replace_nl2customchar', ScopeInterface::SCOPE_STORE)) {
                foreach ($replaceTo as &$_var) {
                    $_var = str_replace("\n", $this->_scopeConfig->getValue('udropship/batch/replace_nl2customchar_value', ScopeInterface::SCOPE_STORE), $_var);
                }
                unset($_var);

            }
            $text = str_replace($replaceFrom, $replaceTo, $text);
        }
        $text = $this->getTemplateFilter()->setVariables($vars)->filter($text);

        return $text;
    }

    public function __destruct()
    {
        $this->_data = [];
    }
}
