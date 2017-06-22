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
     * @var \Bluebadger\Dropship\Api\RateRepositoryInterface
     */
    protected $rateRepository;

    /**
     * Tablerate constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Bluebadger\Dropship\Api\RateRepositoryInterface $rateRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Bluebadger\Dropship\Api\RateRepositoryInterface $rateRepository,
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->rateRepository = $rateRepository;

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
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('title'));

        /* Postcode or state abbreviation depending on country */
        $destCountryId = $request->getDestCountryId();
        if ($destCountryId === self::KEY_COUNTRY_ID_CA) {
            $zoneCode = substr($request->getDestPostcode(), 0, 3);
        } else if ($destCountryId === self::KEY_COUNTRY_ID_US) {
            $zoneCode = $request->getDestRegionCode();
        } else {
            throw new LocalizedException(__('Invalid country ID: ' . $destCountryId));
        }

        $amount = $this->getTotalAmount($request->getAllItems(), $zoneCode);

        $method->setPrice($amount);
        $method->setCost($amount);

        $result->append($method);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedMethods()
    {
        return ['dropship' => $this->getConfigData('name')];
    }

    /**
     *
     */
    private function getTotalAmount(array $items, string $zoneCode)
    {
        $total = 0;

        /** @var Item $item */
        foreach ($items as $item) {
            $rate = $this->rateRepository->getRateByWeightVendorZone(
                $item->getWeight(),
                $item->getData(self::KEY_UDROPSHIP_VENDOR),
                $zoneCode
            );
        }

        /* Update quote items */

        return $total;
    }
}