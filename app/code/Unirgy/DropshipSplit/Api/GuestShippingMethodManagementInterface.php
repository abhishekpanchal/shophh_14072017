<?php

namespace Unirgy\DropshipSplit\Api;

interface GuestShippingMethodManagementInterface
{
    /**
     * List applicable shipping methods for a specified quote.
     *
     * @param string $cartId The shopping cart ID.
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] An array of shipping methods.
     * @throws \Magento\Framework\Exception\NoSuchEntityException The specified quote does not exist.
     * @throws \Magento\Framework\Exception\StateException The shipping address is not set.
     */
    public function getList($cartId);

    /**
     * Estimate shipping
     *
     * @param string $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address The estimate address
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address);
}