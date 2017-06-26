<?php
namespace Bluebadger\Dropship\Model\Carrier\Tablerate;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Class QuoteItemManager
 * @package Bluebadger\Dropship\Model\Carrier\Tablerate
 */
class QuoteItemManager
{
    const KEY_QUOTE_ID = 'quote_id';
    const INT_VAL_DAY = 1;
    const INT_VAL_WEEK = 2;

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
     * @var array
     */
    private $textToInt = [
        'day' => 1,
        'week' => 2
    ];

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
            $product = $this->productRepository->get($cartItemData->getSku(), $storeId);
            $image = $this->imageHelper->init($product, 'product_page_image_small')
                ->setImageFile($product->getFile())
                ->resize(100, 100)
                ->getUrl();
            $quoteItem->setData('name', $cartItemData->getData('name'));
            $quoteItem->setData('sku', $cartItemData->getData('sku'));
            $quoteItem->setData('price', $this->priceHelper->currency($cartItemData->getPrice(), true, false));
            $quoteItem->setData('thumbnail', $image);
            $vendors[$quoteItem->getData('vendor_id')]['items'][] = $quoteItem->getData();
            $vendors[$quoteItem->getData('vendor_id')]['total_qty'] = (int)$cartItemData->getTotalQty();
            $quoteData['total_qty'] += $cartItemData->getTotalQty();
        }

        $quoteData['total_qty'] .= __(' item(s) purchased.');

        foreach ($vendors as &$vendor) {
            $vendor['shipping_cost'] = 0;
            $highestTime = 0;

            foreach ($vendor['items'] as $item) {
                $time = ($item['ship_time_high'] + $item['ship_time_low']) * $this->textToInt[$item['ship_time_unit']];

                if ($time > $highestTime) {
                    $highestTime = $time;
                    $timeText = __($vendor['total_qty'] . ' item(s) ships in approximately ' . $item['ship_time_low'] . '-' . $item['ship_time_high'] . ' business ' . $item['ship_time_unit'] . '(s)');
                    $vendor['time'] = $timeText;
                    $vendor['shipping_cost'] += $item['shipping_cost'];
                }
            }
            if (empty($vendor['shipping_cost'])) {
                $vendor['shipping_cost'] = __('Shipping Cost: Call for a quote');
            } else {
                $vendor['shipping_cost'] = __('Shipping Cost: ' . $this->priceHelper->currency($vendor['shipping_cost'], true, false));
            }
        }

        $vendors = array_values($vendors);
        $quoteData['vendors'] = $vendors;
        $this->appEmulation->stopEnvironmentEmulation();

        return $quoteData;
    }
}