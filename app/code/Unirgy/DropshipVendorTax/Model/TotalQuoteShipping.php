<?php

namespace Unirgy\DropshipVendorTax\Model;

class TotalQuoteShipping extends \Magento\Tax\Model\Sales\Total\Quote\Shipping
{
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $storeId = $quote->getStoreId();
        $items = $shippingAssignment->getItems();
        if (!$items) {
            return $this;
        }

        /** @var \Unirgy\DropshipVendorTax\Helper\Data $udtaxHlp */
        $udtaxHlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\DropshipVendorTax\Helper\Data');
        /** @var \Unirgy\Dropship\Helper\Data $hlp */
        $hlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\Dropship\Helper\Data');
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $shippingAssignment->getShipping()->getAddress();
        /** @var \Unirgy\Dropship\Helper\Item $iHlp */
        $iHlp = \Magento\Framework\App\ObjectManager::getInstance()->get('\Unirgy\Dropship\Helper\Item');
        /** @var \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency */
        $priceCurrency = $hlp->getObj('\Magento\Framework\Pricing\PriceCurrencyInterface');

        $udsDetails = $address->getUdropshipShippingDetails();
        if (!is_array($udsDetails)) {
            $udsDetails = $hlp->unserializeArr($udsDetails);
        }
        if ($hlp->isUdsplitActive()) {
            $_udsDetails = @$udsDetails['methods'];
            if (is_array($_udsDetails)) {
                $dKey = key($_udsDetails);
                if (is_string($dKey)) {
                    $_udsDetails = current($_udsDetails);
                    $_udsDetails = @$_udsDetails['vendors'];
                }
            }
        } else {
            $_udsDetails = false;
            $uMethod = explode('_', $address->getShippingMethod(), 2);
            $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
            if (is_array($udsDetails) && !empty($udsDetails['methods'][$uMethodCode]['vendors'])
                && is_array($udsDetails['methods'][$uMethodCode]['vendors'])
            ) {
                $_udsDetails = $udsDetails['methods'][$uMethodCode]['vendors'];
            }
        }
        if (!is_array($_udsDetails)) {
            $_udsDetails = array();
        }

        $vIds = array();
        foreach ($address->getAllItems() as $_item) {
            $vIds[$iHlp->getUdropshipVendor($_item)] = $iHlp->getUdropshipVendor($_item);
        }
        $vIds = array_filter(array_values($vIds));

        $_udsDetailsEmpty = false;
        if (empty($_udsDetails)) {
            $_udsDetailsEmpty = true;
            $__baseShipping = $_baseShippingFull = $address->getBaseShippingAmount();
            $_baseShippingRound = 0;
            if (count($vIds)>0) {
                $__baseShipping = round($_baseShippingFull/count($vIds),2);
                $_baseShippingRound = $_baseShippingFull-$__baseShipping*count($vIds);
            }
            foreach ($vIds as $idx=>$vId) {
                $_udsDetails[$vId]['price'] = $__baseShipping;
                if ($idx==count($vIds)-1) {
                    $_udsDetails[$vId]['price'] += $_baseShippingRound;
                }
            }
        }

        if ($address->getAddressType()=='billing') {
            $_udsDetails = array();
        }

        $isPriceInclTax = false;
        $shipTaxTotals = array();

        //Add shipping
        $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, false);
        $baseShippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, true);
        if ($shippingDataObject == null || $baseShippingDataObject == null) {
            return $this;
        }

        if (count($vIds)>0 && !empty($_udsDetails)) {
            $__baseDiscount = $_baseDiscountFull = $baseShippingDataObject->getDiscountAmount();
            $_baseDiscountRound = 0;
            if (count($vIds)>0) {
                $__baseDiscount = round($_baseDiscountFull/count($vIds),2);
                $_baseDiscountRound = $_baseDiscountFull-$__baseDiscount*count($vIds);
            }
            foreach ($vIds as $idx=>$vId) {
                $_udsDetails[$vId]['discount'] = $__baseDiscount;
                if ($idx==count($vIds)-1) {
                    $_udsDetails[$vId]['discount'] += $_baseDiscountRound;
                }
            }
        }

        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$shippingDataObject]);
        $baseQuoteDetails = $this->prepareQuoteDetails($shippingAssignment, [$baseShippingDataObject]);

        $__taxDetails = $__baseTaxDetails = null;

        if (!empty($_udsDetails)) {

            foreach ($_udsDetails as $vId => &$_udsDetail) {

                $udtaxHlp->setVendorClassId($shippingDataObject, $vId);
                $udtaxHlp->setVendorClassId($baseShippingDataObject, $vId);
                $baseShippingDataObject->setUnitPrice(@$_udsDetail['price']);
                $baseShippingDataObject->setDiscountAmount(@$_udsDetail['discount']);
                $shippingDataObject->setUnitPrice(
                    $priceCurrency->convert(@$_udsDetail['price'], $storeId)
                );
                $shippingDataObject->setDiscountAmount(
                    $priceCurrency->convert(@$_udsDetail['discount'], $storeId)
                );

                $taxDetails = $this->taxCalculationService
                    ->calculateTax($quoteDetails, $storeId);

                $_taxDetails = $taxDetails->getItems()[self::ITEM_CODE_SHIPPING];
                if (!$__taxDetails) {
                    $__taxDetails = $_taxDetails;
                } else {
                    $__taxDetails->setRowTotal($__taxDetails->getRowTotal() + $_taxDetails->getRowTotal());
                    $__taxDetails->setDiscountTaxCompensationAmount($__taxDetails->getDiscountTaxCompensationAmount() + $_taxDetails->getDiscountTaxCompensationAmount());
                    $__taxDetails->setRowTotalInclTax($__taxDetails->getRowTotalInclTax() + $_taxDetails->getRowTotalInclTax());
                    $__taxDetails->setRowTax($__taxDetails->getRowTax() + $_taxDetails->getRowTax());
                }

                $baseTaxDetails = $this->taxCalculationService
                    ->calculateTax($baseQuoteDetails, $storeId);

                $_baseTaxDetails = $baseTaxDetails->getItems()[self::ITEM_CODE_SHIPPING];

                if (!$__baseTaxDetails) {
                    $__baseTaxDetails = $_baseTaxDetails;
                } else {
                    $__baseTaxDetails->setRowTotal($__baseTaxDetails->getRowTotal() + $_baseTaxDetails->getRowTotal());
                    $__baseTaxDetails->setDiscountTaxCompensationAmount($__baseTaxDetails->getDiscountTaxCompensationAmount() + $_baseTaxDetails->getDiscountTaxCompensationAmount());
                    $__baseTaxDetails->setRowTotalInclTax($__baseTaxDetails->getRowTotalInclTax() + $_baseTaxDetails->getRowTotalInclTax());
                    $__baseTaxDetails->setRowTax($__baseTaxDetails->getRowTax() + $_baseTaxDetails->getRowTax());
                }

                $_udsDetail['price_incl_tax'] = $_baseTaxDetails->getRowTotalInclTax();
                $shipTaxTotals[$vId] = array(
                    'shipping' => $_taxDetails->getRowTotal(),
                    'baseShipping' => $_baseTaxDetails->getRowTotal(),
                    'taxShipping' => $_taxDetails->getRowTax(),
                    'baseTaxShipping' => $_baseTaxDetails->getRowTax(),
                    'isPriceInclTax' => $isPriceInclTax,
                );

            }

            unset($_udsDetail);
            if ($_udsDetailsEmpty) {
                $_udsDetails = array();
            }

            if ($hlp->isUdsplitActive()) {
                $udsDetails['methods'] = $_udsDetails;
            } else {
                $uMethod = explode('_', $address->getShippingMethod(), 2);
                $uMethodCode = !empty($uMethod[1]) ? $uMethod[1] : '';
                if (is_array($udsDetails) && !empty($udsDetails['methods'][$uMethodCode]['vendors'])
                    && is_array($udsDetails['methods'][$uMethodCode]['vendors'])
                ) {
                    $udsDetails['methods'][$uMethodCode]['vendors'] = $_udsDetails;
                }
            }

            $address->setUdropshipShippingDetails($hlp->jsonEncode($udsDetails));
            $address->setVendorShippingTaxDetails($shipTaxTotals);

            $this->processShippingTaxInfo(
                $shippingAssignment,
                $total,
                $__taxDetails,
                $__baseTaxDetails
            );
        } else {
            parent::collect($quote, $shippingAssignment, $total);
        }

        return $this;
    }
}