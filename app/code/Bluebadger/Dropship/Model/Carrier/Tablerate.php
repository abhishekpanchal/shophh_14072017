<?php

namespace Bluebadger\Dropship\Model\Carrier;

use Magento\Framework\Exception\LocalizedException;
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
     * Tablerate constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $rateResourceFactory
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
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRepository;
        $this->rateResourceFactory = $rateResourceFactory;
        $this->quoteItemFactory = $quoteItemFactory;

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

        if ($zoneCode) {
            $amount = $this->getTotalAmount($request->getAllItems(), $zoneCode);
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
    private function getTotalAmount(array $items, string $zoneCode)
    {
        $total = 0;

        /** @var Item $item */
        foreach ($items as $item) {
            /* Make sure the item has an ID */
            if ($item->getId()) {
                $product = $this->productRepository->getById($item->getProduct()->getId());
                $weight = $product->getData(self::KEY_PACKAGE_WEIGHT_LBS);
                $vendorId = $item->getData(self::KEY_UDROPSHIP_VENDOR);

                /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $rateResource */
                $rateResource = $this->rateResourceFactory->create();
                $rateInfo = $rateResource->getRateInfo($weight, $vendorId, $zoneCode);
                $itemTotal = $rateInfo->rate * $item->getQty();

                /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item $quoteItemResource */
                $quoteItemResource = $this->quoteItemFactory->create();
                $quoteItemResource->updateItem($item, $itemTotal);

                $total += $itemTotal;
            }
        }

        return $total;
    }
}