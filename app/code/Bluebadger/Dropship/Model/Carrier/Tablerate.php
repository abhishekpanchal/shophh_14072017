<?php

namespace Bluebadger\Dropship\Model\Carrier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Item;

/**
 * Class Tablerate
 * @package Bluebadger\Dropship\Model\Carrier
 */
class Tablerate extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $rateMethodFactory;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $rateResultFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        ProductRepositoryInterface $productRepository,
        array $data = []
    )
    {
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productRepository = $productRepository;
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

        $method->setCarrier('dropship');
        $method->setCarrierTitle('Dropship');

        $method->setMethod('tablerate');
        $method->setMethodTitle('Table Rate');

        $amount = $this->getTotalAmount(
            $request->getAllItems(),
            $request->getDestCountryId(),
            $request->getDestPostcode()
        );

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
    private function getTotalAmount(array $items, string $countryId, string $postCode)
    {
        $total = 0;

        /* TODO Store these in the database */
        $carrierZones = [
            'H4H' => [
                'Vancouver' => 'H5',
                'Montreal' => 'H5',
                'Toronto' => 'H5',
                'St. Therese' => 'H3',
                'Guelph' => 'H5'
            ]
        ];

        /* TODO Store in database */
        $rates = [
            '1' => [
                'H5' => 7,
                'H3' => 8,
            ],
            '1.5' => [
                'H5' => 9,
                'H3' => 10,
            ],
            '2' => [
                'H5' => 11,
                'H3' => 12,
            ]
        ];

        /** @var Item $item */
        foreach ($items as $item) {
            $product = $this->productRepository->getById($item->getProduct()->getId());
            $weightKg = $product->getData('weight_kg');

            if ($weightKg) {
                $weightIndex = 1;
                $origin = 'Montreal';
                $zone = $carrierZones[substr($postCode, 0, 3)][$origin];
                $total += $rates[$weightIndex][$zone];
            }
        }

        return $total;
    }
}