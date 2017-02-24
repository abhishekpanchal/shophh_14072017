<?php

namespace Unirgy\DropshipSplit\Api;

interface GuestShippingInformationManagementInterface
{
    /**
     * @param string $cartId
     * @param \Unirgy\DropshipSplit\Api\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveAddressInformation(
        $cartId,
        \Unirgy\DropshipSplit\Api\ShippingInformationInterface $addressInformation
    );
}
