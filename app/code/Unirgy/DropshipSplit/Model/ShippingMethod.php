<?php

namespace Unirgy\DropshipSplit\Model;

class ShippingMethod extends \Magento\Quote\Model\Cart\ShippingMethod implements \Unirgy\DropshipSplit\Api\ShippingMethodInterface
{
    /**
     * @return string
     */
    public function getUdropshipVendor()
    {
        return $this->_get(self::KEY_UDROPSHIP_VENDOR);
    }

    /**
     * @param string $udropshipVendor
     * @return $this
     */
    public function setUdropshipVendor($udropshipVendor)
    {
        return $this->setData(self::KEY_UDROPSHIP_VENDOR, $udropshipVendor);
    }

    /**
     * @return string
     */
    public function getUdropshipDefault()
    {
        return $this->_get(self::KEY_UDROPSHIP_DEFAULT);
    }

    /**
     * @param string $udropshipDefault
     * @return $this
     */
    public function setUdropshipDefault($udropshipDefault)
    {
        return $this->setData(self::KEY_UDROPSHIP_DEFAULT, $udropshipDefault);
    }
}