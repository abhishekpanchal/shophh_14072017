<?php

namespace Unirgy\DropshipSplit\Api;

interface TotalsInformationInterface extends \Magento\Checkout\Api\Data\TotalsInformationInterface
{
    const SHIPPING_METHOD_ALL = 'shipping_method_all';
    /**
     *
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[]
     */
    public function getShippingMethodAll();

    /**
     *
     * @param \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] $methods
     * @return $this
     */
    public function setShippingMethodAll($methods);
}