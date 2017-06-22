<?php
namespace Bluebadger\Dropship\Model\Carrier\Tablerate;

use Bluebadger\Dropship\Api\RateRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Class RateRepository
 * @package Bluebadger\Dropship\Model\Carrier\Tablerate
 */
class RateRepository implements RateRepositoryInterface
{
    /**
     * RateRepository constructor.
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory $rateResourceModelFactory
     */
    public function __construct(
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $rateResourceModelFactory
    )
    {
        $this->rateResourceModelFactory = $rateResourceModelFactory;
    }

    /**
     * @inheritdoc
     */
    public function getRateByWeightVendorZone(float $weight, int $vendorId, string $zoneCode)
    {
        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $resourceModel */
        $resourceModel = $this->rateResourceModelFactory->create();
        $rateId = $resourceModel->getRateIdByWeightVendorZone($weight, $vendorId, $zoneCode);

        if (!$rateId) {
            $errorMessage = 'Rate does not exist for weight ' . $weight . ' with vendor ID ';
            $errorMessage .= $vendorId . ' and ' . 'zone ' . $zoneCode;
            throw new NotFoundException($errorMessage);
        }

        $rate = $this->rateFactory->create();
        $rate = $rate->load($rateId);

        return $rate;
    }
}