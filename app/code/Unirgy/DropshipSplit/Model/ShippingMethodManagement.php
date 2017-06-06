<?php

namespace Unirgy\DropshipSplit\Model;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement implements \Unirgy\DropshipSplit\Api\ShippingMethodManagementInterface
{
    /**
     * Constructs a shipping method read service objec
     *
     * @param \Unirgy\Dropship\Helper\Data $udropshipHelper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     */
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {
        $this->_hlp = $udropshipHelper;
        parent::__construct($quoteRepository, $converter, $addressRepository, $totalsCollector);
    }
    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param \Magento\Quote\Api\Data\AddressInterface $address The estimate address
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByExtendedAddress($cartId, \Magento\Quote\Api\Data\AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getEstimatedRates2(
            $quote,
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getRegionId(),
            $address->getRegion()
        );
    }

    /**
     * Estimate shipping
     *
     * @param int $cartId The shopping cart ID.
     * @param int $addressId The estimate address id
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] An array of shipping methods.
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);

        return $this->getEstimatedRates2(
            $quote,
            $address->getCountryId(),
            $address->getPostcode(),
            $address->getRegionId(),
            $address->getRegion()
        );
    }

    /**
     * Get estimated rates
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @return \Unirgy\DropshipSplit\Api\ShippingMethodInterface[] An array of shipping methods.
     */
    protected function getEstimatedRates2(\Magento\Quote\Model\Quote $quote, $country, $postcode, $regionId, $region)
    {
        $outputByVendor = [];
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCountryId($country);
        $shippingAddress->setPostcode($postcode);
        $shippingAddress->setRegionId($regionId);
        $shippingAddress->setRegion($region);
        $shippingAddress->setCollectShippingRates(true);
        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $details = $shippingAddress->getUdropshipShippingDetails();
        if ($details) {
            $details = $this->_hlp->unserializeArr($details);
            $methods = isset($details['methods']) ? $details['methods'] : array();
        }
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        $output = [];
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                if (!$rate->getUdropshipVendor() && $rate->getCarrier()!='udsplit') {
                    continue;
                }
                $vId = $rate->getUdropshipVendor();
                $__out = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
                $__out->setUdropshipVendor($vId);
                if ($rate->getCarrier()=='udsplit') {
                    $__out->setUdropshipVendor(0);
                    $output[] = $__out;
                } else {
                    if (empty($outputByVendor[$vId])) {
                        $v = $this->_hlp->getVendor($vId);
                        $__outHeader = $this->_hlp->createObj('\Unirgy\DropshipSplit\Model\ShippingMethod');
                        $__outHeader->setCarrierCode(null);
                        $__outHeader->setMethodCode(null);
                        $__outHeader->setCarrierTitle($v->getVendorName());
                        $__outHeader->setMethodTitle($v->getFormatedAddress('text_small'));
                        $__outHeader->setUdropshipVendor(-1);
                        $outputByVendor[$vId][] = $__outHeader;
                    }
                    if ($methods[$vId] && $rate->getCarrier().'_'.$rate->getMethod() == @$methods[$vId]['code']) {
                        $__out->setUdropshipDefault(1);
                    } else {
                        $__out->setUdropshipDefault(0);
                    }
                    $outputByVendor[$vId][] = $__out;
                }
            }
        }
        foreach ($outputByVendor as $vId => $__outByVid) {
            foreach ($__outByVid as $out) {
                $output[] = $out;
            }
        }
        return $output;
    }
}