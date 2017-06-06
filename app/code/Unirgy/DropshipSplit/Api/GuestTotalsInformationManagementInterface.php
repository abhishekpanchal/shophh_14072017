<?php

namespace Unirgy\DropshipSplit\Api;

interface GuestTotalsInformationManagementInterface
{
    /**
     * Calculate quote totals based on address and shipping method.
     *
     * @param string $cartId
     * @param \Unirgy\DropshipSplit\Api\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        \Unirgy\DropshipSplit\Api\TotalsInformationInterface $addressInformation
    );
}