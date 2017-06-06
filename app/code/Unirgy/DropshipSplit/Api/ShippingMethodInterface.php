<?php

namespace Unirgy\DropshipSplit\Api;

interface ShippingMethodInterface extends \Magento\Quote\Api\Data\ShippingMethodInterface
{
    const KEY_UDROPSHIP_VENDOR='udropship_vendor';
    const KEY_UDROPSHIP_DEFAULT='udropship_default';
    /**
     * @return string
     */
    public function getUdropshipVendor();

    /**
     * @param string $udropshipVendor
     * @return $this
     */
    public function setUdropshipVendor($udropshipVendor);

    /**
     * @return string
     */
    public function getUdropshipDefault();

    /**
     * @param string $udropshipDefault
     * @return $this
     */
    public function setUdropshipDefault($udropshipDefault);
}