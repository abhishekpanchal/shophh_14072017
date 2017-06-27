<?php

namespace Bluebadger\Dropship\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Tablerate
 * @package Bluebadger\Dropship\Model\Carrier
 */
class Tablerate extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const KEY_COUNTRY_ID_CA = 'CA';
    const KEY_COUNTRY_ID_US = 'US';
    const KEY_UDROPSHIP_VENDOR = 'udropship_vendor';
    const KEY_PACKAGE_WEIGHT_LBS = 'package_weight_lbs';
    const KEY_TITLE = 'title';
    const KEY_VENDOR_ID = 'vendor_id';
    const KEY_CALL_FOR_QUOTE = 'call_for_quote';

    /**
     * @var string
     */
    protected $_code = 'dropshiptablerate';

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate
     */
    protected $rateResourceFactory;

    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\ItemFactory
     */
    protected $quoteItemFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * Tablerate constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory $rateResourceFactory
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\ItemFactory $quoteItemFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory $rateResourceFactory,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\ItemFactory $quoteItemFactory,
        \Magento\Checkout\Model\Session $session,
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRepository;
        $this->rateResourceFactory = $rateResourceFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->session = $session;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @inheritdoc
     */
    public function collectRates(RateRequest $request)
    {
        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData(self::KEY_TITLE));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData(self::KEY_TITLE));
        $zoneCode = substr($request->getDestPostcode(), 0, 3);
        $vendorId = $request->getData(self::KEY_VENDOR_ID);

        if ($zoneCode && $vendorId) {
            $amount = $this->getTotalAmount($this->session->getQuote()->getAllItems(), $zoneCode, $vendorId);
            $method->setPrice($amount);
            $method->setCost($amount);
            $result->append($method);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedMethods()
    {
        return ['dropshiptablerate' => $this->getConfigData('name')];
    }

    /**
     * @param array $items
     * @param string $zoneCode
     * @return int
     */
    private function getTotalAmount(array $items, string $zoneCode, int $vendorId)
    {
        $total = 0;
        $isQuoteCleaned = false;

        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item $quoteItemResource */
        $quoteItemResource = $this->quoteItemFactory->create();
        $weights = [];
        $vendorItems = [];

        /** @var Item $item */
        foreach ($items as $item) {
            /* The item might not have been saved yet */
            if (!$item->getId() || $item->getData(self::KEY_UDROPSHIP_VENDOR) != $vendorId) {
                continue;
            }

            /* Skip configurables */
            if ($item->getProductType() == 'configurable') {
                continue;
            }
            if (!$isQuoteCleaned) {
                //$quoteItemResource->deletByQuoteId($item->getQuote()->getId());
                $isQuoteCleaned = true;
            }
            $product = $this->productRepository->getById($item->getProduct()->getId());

            /** TODO Randomly generated dates for testing purposes */
            $item->setData('ship_time_low', rand(1, 4));
            $item->setData('ship_time_high', rand(5, 10));
            $item->setData('ship_time_unit', rand(1, 2));

            if (!isset($weights[$vendorId])) {
                $weights[$vendorId] = 0;
            }
            $weightQty = (float)$product->getData(self::KEY_PACKAGE_WEIGHT_LBS) * (float)$item->getQty();
            $item->setData(self::KEY_PACKAGE_WEIGHT_LBS, $weightQty);
            $weights[$vendorId] = (float)$weights[$vendorId] + $weightQty;
            $vendorItems[$vendorId][] = $item;
        }

        $shippingCost = 0;

        foreach ($weights as $vendorId => $weight) {
            $callForQuote = false;

            /* Check if there is a least one 'call for quote' for current vendor */
            foreach ($vendorItems[$vendorId] as $vendorItem) {
                $product = $this->productRepository->getById($vendorItem->getProduct()->getId());
                $callForQuote = ($product->getData(self::KEY_CALL_FOR_QUOTE)) ? true : false;
                if ($callForQuote) {
                    break;
                }
            }

            if (!$callForQuote) {
                /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $rateResource */
                $rateResource = $this->rateResourceFactory->create();
                $rateInfo = $rateResource->getRateInfo($weight, $vendorId, $zoneCode);
                $shippingCost += $rateInfo->rate;
                $total += $shippingCost;
            }

            /* Set rate for each item */
            foreach ($vendorItems[$vendorId] as $vendorItem) {
                $quoteItemResource->updateItem($vendorItem, $shippingCost, $callForQuote);
            }
        }

        return $total;
    }
}