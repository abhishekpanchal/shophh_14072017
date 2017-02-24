<?php

namespace Unirgy\DropshipBatch\Model\Adapter;

use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\ItemFactory as OrderItemFactory;
use Magento\Sales\Model\Order\Shipment\ItemFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\TemplateFilterFactory;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Helper\Item;

class DefaultAdapter
    extends AbstractAdapter
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * @var Config
     */
    protected $_catalogConfig;

    public function __construct(
        StoreManagerInterface $modelStoreManagerInterface, 
        ProductFactory $modelProductFactory, 
        Config $catalogConfig,
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
        $this->_storeManager = $modelStoreManagerInterface;
        $this->_productFactory = $modelProductFactory;
        $this->_catalogConfig = $catalogConfig;

        parent::__construct($batchHelper, $orderFactory, $dropshipHelper, $helperItem, $eventManager, $helperMessage, $shipmentItemFactory, $orderItemFactory, $templateFilterFactory, $scopeConfig, $data);
    }

    public function addRowLog($order, $po, $poItem)
    {
        $this->getBatch()->addRowLog($order, $po, $poItem);
        return $this;
    }
    public function addPO($po)
    {
        if (!$this->preparePO($po)) {
            return $this;
        }

        if (!$this->getItemsArr()) {
            $this->setItemsArr([]);
            $this->setTotalsArr([]);
        }
        $itemsFooter = $itemsFooterTpl = '';
        $itemTpl = $tpl = $this->getExportTemplate();

        if (($useItemTemplate = $this->getUseItemExportTemplate())) {
            $itemTpl = $this->getItemExportTemplate();
        }
        $itemsFooterTpl = $this->getItemFooterExportTemplate();

        $productIds = [];
        $udbatchLineNumber = 0;
        foreach ($po->getItemsCollection() as $item) {
            $orderItem = $item->getOrderItem();
            if (($orderItem->getChildren() || $orderItem->getChildrenItems() and $orderItem->getProductType() != \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                || ($orderItem->getParentItem() and $orderItem->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
                || in_array($orderItem->getStatusId(), $this->getSkipItemStatuses())) {
                continue;
            }
            $productIds[] = $item->getProductId();
            $item->setUdbatchLineNumber(++$udbatchLineNumber);
        }
        $po->setUdbatchTotalLines($udbatchLineNumber);

        $_oldStoreId = $this->_storeManager->getStore()->getId();
        $this->_storeManager->setCurrentStore($this->_storeManager->getDefaultStoreView());
        $products = $this->_productFactory->create()->getCollection()
            ->addIdFilter($productIds)
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite();

        $products->load();
        $this->_storeManager->setCurrentStore($_oldStoreId);

        $poIdKey = $po->getIncrementId();
        $idx = 0;
        foreach ($po->getItemsCollection() as $item) {
            if ($product = $products->getItemById($item->getProductId())) {
                $item->setProductForBatch($product);
            }
            if (!$this->preparePOItem($item)) {
                continue;
            }
            $itemKey = $poIdKey.'-'.$item->getId();
            if ($useItemTemplate) {
                if (0==$idx++) {
                    $this->_data['items_arr'][$poIdKey.'-0'] = $this->renderTemplate($tpl, $this->getVars());
                }
            }
            $itemsFooter = $this->renderTemplate($itemsFooterTpl, $this->getVars());
            $this->_data['items_arr'][$itemKey] = $this->renderTemplate($itemTpl, $this->getVars());
            $this->addRowLog($this->getOrder(), $this->getPo(), $this->getPoItem());
            $this->restoreItem();
        }
        $this->_data['totals_arr'][$poIdKey] = $this->getVars('po_totals');
        if ($itemsFooter) {
            $this->_data['items_arr'][$poIdKey.'-99999'] = $itemsFooter;
        }

        $this->setHasOutput(true);
        return $this;
    }

    public function renderOutput()
    {
        $batch = $this->getBatch();
        $header = $batch->getBatchType()=='export_orders' ? $this->getBatchExportOrdersHeader() : '';

        if ($this->getBatchExportOrdersTotalsTemplate()) {
            $grandTotals  = [];
            foreach ($this->_data['totals_arr'] as $poTotals) {
                foreach ($poTotals as $poTotKey=>$poTotVal) {
                    if (!isset($grandTotals[$poTotKey])) {
                        $grandTotals[$poTotKey] = $poTotVal;
                    } else {
                        $grandTotals[$poTotKey] += $poTotVal;
                    }
                }
            }
            $this->_data['items_arr'][] = $this->renderTemplate($this->getBatchExportOrdersTotalsTemplate(), ['grand_totals'=>$grandTotals]);
        }
        if ($batch->getStatement() && $batch->getStatement()->getExtraAdjustments()) {
            $this->_data['items_arr'][] = '';
            $this->_data['items_arr'][] = __('Adjustments');
            foreach ($batch->getStatement()->getExtraAdjustments() as $stAdj) {
                $this->_data['items_arr'][] = '"'.implode('","', [
                        $stAdj['adjustment_id'], $stAdj['po_type'], $stAdj['comment'],
                        $this->_hlp->formatDate($stAdj['created_at'], \IntlDateFormatter::SHORT), $stAdj['amount']
                ]).'"';
            }
            $this->_data['items_arr'][] = '';
            $this->_data['items_arr'][] = __('Statement Totals');
            $this->_data['items_arr'][] = '"'.implode('","', [
                    __('Total Payout'), $batch->getStatement()->getTotalPayout()
                ]).'"';
            if ($this->_bHlp->isUdpayoutActive()) {
                $this->_data['items_arr'][] = '"'.implode('","', [
                        __('Total Paid'), $batch->getStatement()->getTotalPaid()
                    ]).'"';
                $this->_data['items_arr'][] = '"'.implode('","', [
                        __('Total Due'), $batch->getStatement()->getTotalDue()
                    ]).'"';
            }
        }

        $this->setHasOutput(false);
        $output = ($header ? $header."\n" : '') . join("\n", $this->getItemsArr());
        return $output;
    }

    public function getPerPoOutput()
    {
        $batch = $this->getBatch();
        $rows = [];
        $rows['header'] = $batch->getBatchType()=='export_orders' ? $this->getBatchExportOrdersHeader() : '';

        foreach ($this->getItemsArr() as $iKey => $iRow) {
            $poId = substr($iKey, 0, strpos($iKey, '-'));
            if (empty($rows[$poId])) {
                $rows[$poId] = '';
            } else {
                $rows[$poId] .= "\n";
            }
            $rows[$poId] .= $iRow;
        }

        $this->setHasOutput(false);

        return $rows;
    }

}
