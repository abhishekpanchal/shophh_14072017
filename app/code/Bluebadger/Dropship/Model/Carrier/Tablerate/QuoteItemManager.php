<?php
namespace Bluebadger\Dropship\Model\Carrier\Tablerate;
use Bluebadger\Dropship\Model\Carrier\Tablerate;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class QuoteItemManager
 * @package Bluebadger\Dropship\Model\Carrier\Tablerate
 */
class QuoteItemManager
{
    const KEY_QUOTE_ID = 'quote_id';
    const KEY_WEEK = 'weeks';

    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\Collection
     */
    protected $quoteItemCollectionFactory;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $priceHelper;

    /**
     * QuoteItemManager constructor.
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Magento\Catalog\Helper\ImageFactory $imageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     */
    public function __construct(
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Helper\Image $imageHelper,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\Helper\Data $priceHelper
    )
    {
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Return an array of quote items sorted by vendor.
     * @param $quoteId
     * @return array
     */
    public function getQuoteItemsSortedByVendor($quoteId)
    {
        $quoteData = [];
        $storeId = $this->storeManager->getStore()->getId();
        $this->appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $quoteData['total_qty'] = 0;
        $vendors = [];

        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\Collection $quoteItems */
        $quoteItems = $this->quoteItemCollectionFactory->create();
        $quoteItems->addFieldToFilter(self::KEY_QUOTE_ID, $quoteId);

        /** @var \Bluebadger\Dropship\Model\Carrier\Tablerate\Quote\Item $quoteItem */
        foreach ($quoteItems as $quoteItem) {
            $cartItemData = $quoteItem->getCartItem();

            /* Remove bogus items from quote item table */
            try {
                $product = $this->productRepository->get($cartItemData->getSku(), $storeId);
            } catch (\Exception $e) {
                $quoteItem->delete();
                continue;
            }

            $image = $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($product->getFile())
                ->resize(100, 100)
                ->getUrl();
            $quoteItem->setData('name', $cartItemData->getData('name'));
            $quoteItem->setData('sku', $cartItemData->getData('sku'));
            $quoteItem->setData('price', $this->priceHelper->currency($product->getPrice() * $cartItemData->getTotalQty(), true, false));

            /* Add size */
            $attr = $product->getResource()->getAttribute('size');
            if ($attr->usesSource()) {
                $size = $attr->getSource()->getOptionText($product->getData('size'));
                if ($size) {
                    $quoteItem->setData('size', $size);
                }
            }

            /* Add color */
            $attr = $product->getResource()->getAttribute('color');
            if ($attr->usesSource()) {
                $color = $attr->getSource()->getOptionText($product->getData('color'));
                if ($color) {
                    $quoteItem->setData('color', $color);
                }
            }

            /* Format qty */
            $quoteItem->setData('qty', (int)$cartItemData->getQty());

            $quoteItem->setData('thumbnail', $image);

            /* Check if item is call for quote */
            $type = ($quoteItem->getData('call_for_quote')) ? 'call_for_quote' : 'rate';
            $vendorId = $quoteItem->getData('vendor_id');

            if (!isset($vendors[$vendorId])) {
                $vendors[$vendorId] = [];
            }
            if (!isset($vendors[$vendorId][$type])) {
                $vendors[$vendorId][$type] = [];
            }
            if (!isset($vendors[$vendorId][$type]['items'])) {
                $vendors[$vendorId][$type]['items'] = [];
            }
            $vendors[$vendorId][$type]['items'][] = $quoteItem->getData();

            if (!isset($vendors[$vendorId][$type]['total_qty'])) {
                $vendors[$vendorId][$type]['total_qty'] = (int)0;
            }

            $vendors[$vendorId][$type]['total_qty'] += (int)$cartItemData->getTotalQty();
            $quoteData['total_qty'] += $cartItemData->getTotalQty();
        }

        $quoteData['total_qty'] .= __(' item(s) purchased.');

        $combinedVendors = [];

        /** @var array $vendor */
        foreach ($vendors as $key => $vendor) {
            if (isset($vendor['call_for_quote'])) {
                $vendor['call_for_quote']['shipping_time_text'] = $this->getShippingTimeText(
                    $vendor['call_for_quote']['items'],
                    $vendor['call_for_quote']['total_qty']
                );
                $vendor['call_for_quote']['shipping_cost_class'] = 'call-for-quote';
                $vendor['call_for_quote']['shipping_cost_text'] = __('Call for quote.');
                $combinedVendors[] = $vendor['call_for_quote'];

            if (isset($vendor['rate'])) {
                $vendor['rate']['shipping_time_text'] = $this->getShippingTimeText(
                    $vendor['rate']['items'],
                    $vendor['rate']['total_qty']
                );
                $vendor['rate']['shipping_cost_class'] = '';
                $vendor['rate']['shipping_cost_text'] = $this->getShippingCostText($vendor['rate']['items']);
                $combinedVendors[] = $vendor['rate'];
            }
        }

        $quoteData['vendors'] = $combinedVendors;
        $this->appEmulation->stopEnvironmentEmulation();

        return $quoteData;
    }

    /**
     * @param array $items
     * @param int $totalQty
     * @return \Magento\Framework\Phrase
     */
    private function getShippingTimeText(array $items, int $totalQty)
    {
        $shippingTime = 0;
        $highestTime = 0;

        /** @var array $item */
        foreach ($items as $item) {
            $days = ($item['ship_time_unit'] == self::KEY_WEEK) ? 5 : 1;
            $time = $item['ship_time_high'] * $days;

            if ($time > $highestTime) {
                $highestTime = $time;
                $shippingTime = __($totalQty . ' item(s) will ship in approximately ' . $item['ship_time_low'] . '-' . $item['ship_time_high'] . ' business ' . substr($item['ship_time_unit'], 0, -1) . '(s).');
            }
        }

        return $shippingTime;
    }

    /**
     * @param array $items
     */
    private function getShippingCostText(array $items)
    {
        $isFree = false;
        $shippingCost = 0;

        foreach ($items as $item) {
            if ($item['is_free']) {
                $isFree = true;
                break;
            }

            $shippingCost += $item['shipping_cost'];
        }

        if ($isFree) {
            $shippingCostText = __('FREE');
        } else if (empty($shippingCost)) {
            $shippingCostText = __('Call for quote');
        } else {
            $shippingCostText = __('Shipping Cost: ' . $this->priceHelper->currency($shippingCost, true, false));
        }

        return $shippingCostText;
    }
}
