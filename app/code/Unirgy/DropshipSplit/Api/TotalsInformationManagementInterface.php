<?php

namespace Unirgy\DropshipSplit\Api;

interface TotalsInformationManagementInterface
{
    /**
     * Calculate quote totals based on address and shipping method.
     *
     * @param int $cartId
     * @param \Unirgy\DropshipSplit\Api\TotalsInformationInterface $addressInformation
     * @return \Magento\Quote\Api\Data\TotalsInterface
     */
    public function calculate(
        $cartId,
        \Unirgy\DropshipSplit\Api\TotalsInformationInterface $addressInformation
    );
}