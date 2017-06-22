<?php
namespace Bluebadger\Dropship\Api;

/**
 * Interface RateRepositoryInterface
 * @package Bluebadger\Dropship\Api
 */
interface RateRepositoryInterface
{
    /**
     * @param float $weight
     * @param int $vendorId
     * @param string $zoneCode
     * @return mixed
     */
    public function getRateByWeightVendorZone(float $weight, int $vendorId, string $zoneCode);
}