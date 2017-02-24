<?php

namespace Unirgy\DropshipSplit\Api;

interface ShippingInformationInterface extends \Magento\Checkout\Api\Data\ShippingInformationInterface
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