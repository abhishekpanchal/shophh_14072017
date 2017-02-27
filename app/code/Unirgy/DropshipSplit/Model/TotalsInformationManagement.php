<?php

namespace Unirgy\DropshipSplit\Model;

class TotalsInformationManagement implements \Unirgy\DropshipSplit\Api\TotalsInformationManagementInterface
{
    /**
     * Cart total repository.
     *
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
    }
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
    ) {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $this->validateQuote($quote);

        if ($quote->getIsVirtual()) {
            $quote->setBillingAddress($addressInformation->getAddress());
            $quote->collectTotals();
        } else {
            $quote->setShippingAddress($addressInformation->getAddress());
            $quote->getShippingAddress()->setCollectShippingRates(true);
            /*
            $quote->getShippingAddress()->setShippingMethod(
                $addressInformation->getShippingCarrierCode() . '_' . $addressInformation->getShippingMethodCode()
            );
            */
            $methods = [];
            foreach ($addressInformation->getShippingMethodAll() as $__sm) {
                if ($__sm->getUdropshipVendor()>0) {
                    $methods[$__sm->getUdropshipVendor()] = $__sm->getCarrierCode().'_'.$__sm->getMethodCode();
                }
            }
            $quote->getShippingAddress()->setShippingMethod($methods);
            $quote->collectTotals();
        }

        return $this->cartTotalRepository->get($cartId);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function validateQuote(\Magento\Quote\Model\Quote $quote)
    {
        if ($quote->getItemsCount() === 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Totals calculation is not applicable to empty cart')
            );
        }
    }

}