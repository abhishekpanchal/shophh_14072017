<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ExportStockpo;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Model\TemplatefilterFactory;
use Unirgy\Dropship\Helper\Data as HelperData;

abstract class AbstractExportStockpo extends DataObject
{
    /**
     * @var OrderFactory
     */
    protected $_modelOrderFactory;

    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var ManagerInterface
     */
    protected $_eventManagerInterface;

    /**
     * @var ItemFactory
     */
    protected $_shipmentItemFactory;

    /**
     * @var OrderItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @var TemplatefilterFactory
     */
    protected $_modelTemplatefilterFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_configScopeConfigInterface;

    public function __construct(array $data = [], 
        OrderFactory $modelOrderFactory, 
        HelperData $helperData, 
        ManagerInterface $eventManagerInterface, 
        ItemFactory $shipmentItemFactory, 
        OrderItemFactory $orderItemFactory, 
        TemplatefilterFactory $modelTemplatefilterFactory, 
        ScopeConfigInterface $configScopeConfigInterface)
    {
        $this->_modelOrderFactory = $modelOrderFactory;
        $this->_helperData = $helperData;
        $this->_eventManagerInterface = $eventManagerInterface;
        $this->_shipmentItemFactory = $shipmentItemFactory;
        $this->_orderItemFactory = $orderItemFactory;
        $this->_modelTemplatefilterFactory = $modelTemplatefilterFactory;
        $this->_configScopeConfigInterface = $configScopeConfigInterface;

        parent::__construct($data);
    }

    public function init()
    {
        $this->setHasOutput(false);
        return $this;
    }

    abstract public function addPO($po);

    abstract public function renderOutput();

    public function preparePO($po)
    {
        $order = $po->getOrder();
        if (!$order->getEntityId()) {
            $order = $this->_modelOrderFactory->create()->load($po->getOrderId() ? $po->getOrderId() : $po->getParentId());
        }
        $this->setOrder($order);
        $this->setPo($po);
        $this->setStockPo($po->getStockPo());
        $this->_helperData->addVendorSkus($po);

        $vars = [
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $this->getOrder()->getBillingAddress(),
            'shipping' => $this->getOrder()->getShippingAddress(),
            'po' => $this->getPo(),
            'po_id' => $this->getPo()->getIncrementId(),
            'stockpo' => $this->getStockPo(),
            'stockpo_id' => $this->getStockPo()->getIncrementId(),
            'stock_vendor' => $this->getPo()->getStockVendor(),
            'vendor' => $this->getPo()->getVendor(),
        ];
        $this->_eventManagerInterface->dispatch('udbatch_prepare_stockpo', ['vars'=>&$vars]);
        $this->setVars($vars);

        return true;
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

        if (($orderItem->getChildren() || $orderItem->getChildrenItems() and $orderItem->getProductType() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            || ($orderItem->getParentItem() and $orderItem->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
            || in_array($orderItem->getStatusId(), $this->getBatch()->getSkipItemStatuses())) {
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
        $vars = [
            'order' => $this->getOrder(),
            'order_id' => $this->getOrder()->getIncrementId(),
            'billing' => $this->getOrder()->getBillingAddress(),
            'shipping' => $this->getOrder()->getShippingAddress(),
            'po' => $this->getPo(),
            'po_id' => $this->getPo()->getIncrementId(),
            'stockpo' => $this->getStockPo(),
            'stockpo_id' => $this->getStockPo()->getIncrementId(),
            'stock_vendor' => $this->getPo()->getStockVendor(),
            'vendor' => $this->getPo()->getVendor(),

            'item' => $this->getPoItem(),
            'order_item' => $this->getOrderItem(),
            'product_options' => join('; ', $productOptionsArr),
        ];
        $this->_eventManagerInterface->dispatch('udbatch_prepare_stockpo_item', ['vars'=>&$vars]);
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
        return $this;
    }

    public function getTemplateFilter()
    {
        if (empty($this->_templateFilter)) {
            $this->_templateFilter = $this->_modelTemplatefilterFactory->create();
        }
        return $this->_templateFilter;
    }

    public function getExportTemplate()
    {
        if (!$this->hasData('export_template')) {
        	$exportTpl = $this->getBatch()->getVendor()->getBatchExportStockpoTemplate();
        	if (trim($exportTpl) == '') {
        		throw new \Exception(__('Empty Export Template'));
        	}
            $this->setData('export_template', $exportTpl);
        }
        return $this->getData('export_template');
    }

    public function renderTemplate($text, $vars)
    {
        if (preg_match_all('#\[([a-z0-9._]+)\]#i', $text, $m, PREG_PATTERN_ORDER)) {
            $keys = array_unique($m[1]);
            $replaceFrom = [];
            $replaceTo = [];
            foreach ($keys as $key) {
                $keyArr = explode('.', $key);
                $value = $vars;
                foreach ($keyArr as $k) {
                    if (!isset($value[$k])) {
                        $value = '';
                        break;
                    }
                    $value = $value[$k];
                }

                $replaceFrom[] = '['.$key.']';
                $replaceTo[] = is_numeric($value) ? $value*1 : $value;
            }
            if ($this->_configScopeConfigInterface->isSetFlag('udropship/batch/replace_nl2customchar', ScopeInterface::SCOPE_STORE)) {
                foreach ($replaceTo as &$_var) {
                    $_var = str_replace("\n", $this->_configScopeConfigInterface->getValue('udropship/batch/replace_nl2customchar_value', ScopeInterface::SCOPE_STORE), $_var);
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
