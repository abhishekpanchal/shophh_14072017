<?php

namespace Unirgy\DropshipSplit\Api;

interface ShippingInformationManagementInterface
{
    /**
     * @param int $cartId
     * @param \Unirgy\DropshipSplit\Api\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveAddressInformation(
        $cartId,
        \Unirgy\DropshipSplit\Api\ShippingInformationInterface $addressInformation
    );
}