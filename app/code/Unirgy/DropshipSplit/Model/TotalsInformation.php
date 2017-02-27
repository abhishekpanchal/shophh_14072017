<?php

namespace Unirgy\DropshipSplit\Model;

class TotalsInformation extends \Magento\Checkout\Model\TotalsInformation implements \Unirgy\DropshipSplit\Api\TotalsInformationInterface
{
    /**
     *
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[]
     */
    public function getShippingMethodAll()
    {
        return $this->getData(self::SHIPPING_METHOD_ALL);
    }

    /**
     *
     * @param \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] $methods
     * @return $this
     */
    public function setShippingMethodAll($methods)
    {
        return $this->setData(self::SHIPPING_METHOD_ALL, $methods);
    }
}