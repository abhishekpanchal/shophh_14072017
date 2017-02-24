<?php

namespace Unirgy\DropshipVendorTax\Model;

class TotalQuoteTax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $baseTaxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, true);
        $taxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, false);

        //Populate address and items with tax calculation results
        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);
        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING];
            $__taxDetails = $__baseTaxDetails = null;
            foreach ($shippingTaxDetails as $__shipDetail) {
                $_taxDetails = $__shipDetail[self::KEY_ITEM];
                $_baseTaxDetails = $__shipDetail[self::KEY_BASE_ITEM];
                if (!$__taxDetails) {
                    $__taxDetails = $_taxDetails;
                    $__baseTaxDetails = $_baseTaxDetails;
                } else {
                    $__taxDetails->setRowTotal($__taxDetails->getRowTotal()+$_taxDetails->getRowTotal());
                    $__taxDetails->setDiscountTaxCompensationAmount($__taxDetails->getDiscountTaxCompensationAmount()+$_taxDetails->getDiscountTaxCompensationAmount());
                    $__taxDetails->setRowTotalInclTax($__taxDetails->getRowTotalInclTax()+$_taxDetails->getRowTotalInclTax());
                    $__taxDetails->setRowTax($__taxDetails->getRowTax()+$_taxDetails->getRowTax());
                    $__baseTaxDetails->setRowTotal($__baseTaxDetails->getRowTotal()+$_baseTaxDetails->getRowTotal());
                    $__baseTaxDetails->setDiscountTaxCompensationAmount($__baseTaxDetails->getDiscountTaxCompensationAmount()+$_baseTaxDetails->getDiscountTaxCompensationAmount());
                    $__baseTaxDetails->setRowTotalInclTax($__baseTaxDetails->getRowTotalInclTax()+$_baseTaxDetails->getRowTotalInclTax());
                    $__baseTaxDetails->setRowTax($__baseTaxDetails->getRowTax()+$_baseTaxDetails->getRowTax());
                }
            }
            $this->processShippingTaxInfo($shippingAssignment, $total, $__taxDetails, $__baseTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($total, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }

        return $this;
    }
    protected function getQuoteTaxDetails($shippingAssignment, $total, $useBaseCurrency)
    {
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

        $shipTaxTotals = $address->getVendorShippingTaxDetails();

        $vIds = array();
        foreach ($address->getAllItems() as $_item) {
            $vIds[$iHlp->getUdropshipVendor($_item)] = $iHlp->getUdropshipVendor($_item);
        }
        $vIds = array_filter(array_values($vIds));

        if (count($vIds)>0 && !empty($shipTaxTotals)) {
            $__baseDiscount = $_baseDiscountFull = $total->getBaseShippingDiscountAmount();
            $_baseDiscountRound = 0;
            $__discount = $_discountFull = $total->getShippingDiscountAmount();
            $_discountRound = 0;
            if (count($vIds)>0) {
                $__baseDiscount = round($_baseDiscountFull/count($vIds),2);
                $_baseDiscountRound = $_baseDiscountFull-$__baseDiscount*count($vIds);
                $__discount = round($_discountFull/count($vIds),2);
                $_discountRound = $_discountFull-$__discount*count($vIds);
            }
            foreach ($vIds as $idx=>$vId) {
                $shipTaxTotals[$vId]['baseDiscount'] = $__baseDiscount;
                $shipTaxTotals[$vId]['discount'] = $__discount;
                if ($idx==count($vIds)-1) {
                    $shipTaxTotals[$vId]['baseDiscount'] += $_baseDiscountRound;
                    $shipTaxTotals[$vId]['discount'] += $_discountRound;
                }
            }
        }
        $address->setVendorShippingTaxDetails($shipTaxTotals);

        //Setup taxable items
        $priceIncludesTax = $this->_config->priceIncludesTax($address->getQuote()->getStore());
        $itemDataObjects = $this->mapItems($shippingAssignment, $priceIncludesTax, $useBaseCurrency);

        $origTotals = [$total->getShippingAmount(), $total->getBaseShippingAmount(), $total->getBaseShippingDiscountAmount(), $total->getShippingDiscountAmount(), $total->getShippingTaxCalculationAmount(), $total->getBaseShippingTaxCalculationAmount()];

        if (!empty($shipTaxTotals)) {
            foreach ($shipTaxTotals as $vId => $shipTaxTotal) {
                $total->setShippingAmount(@$shipTaxTotal['shipping']);
                $total->setBaseShippingAmount(@$shipTaxTotal['baseShipping']);
                $total->setBaseShippingDiscountAmount(@$shipTaxTotal['baseDiscount']);
                $total->setShippingDiscountAmount(@$shipTaxTotal['discount']);
                $total->setShippingTaxCalculationAmount(@$shipTaxTotal['shipping']);
                $total->setBaseShippingTaxCalculationAmount(@$shipTaxTotal['baseShipping']);
                $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
                $udtaxHlp->setVendorClassId($shippingDataObject, $vId);
                if ($shippingDataObject != null) {
                    $itemDataObjects[] = $shippingDataObject->setCode($shippingDataObject->getCode() . '-' . $vId);
                }
            }
        } else {
            $shippingDataObject = $this->getShippingDataObject($shippingAssignment, $total, $useBaseCurrency);
            if ($shippingDataObject != null) {
                $itemDataObjects[] = $shippingDataObject;
            }
        }
        $total->setShippingAmount($origTotals[0]);
        $total->setBaseShippingAmount($origTotals[1]);
        $total->setBaseShippingDiscountAmount($origTotals[2]);
        $total->setShippingDiscountAmount($origTotals[3]);
        $total->setShippingTaxCalculationAmount($origTotals[4]);
        $total->setBaseShippingTaxCalculationAmount($origTotals[5]);

        //process extra taxable items associated only with quote
        $quoteExtraTaxables = $this->mapQuoteExtraTaxables(
            $this->quoteDetailsItemDataObjectFactory,
            $address,
            $useBaseCurrency
        );
        if (!empty($quoteExtraTaxables)) {
            $itemDataObjects = array_merge($itemDataObjects, $quoteExtraTaxables);
        }

        //Preparation for calling taxCalculationService
        $quoteDetails = $this->prepareQuoteDetails($shippingAssignment, $itemDataObjects);

        $taxDetails = $this->taxCalculationService
            ->calculateTax($quoteDetails, $address->getQuote()->getStore()->getStoreId());

        return $taxDetails;
    }
}