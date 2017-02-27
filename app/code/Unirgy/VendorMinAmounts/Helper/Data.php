<?php

namespace Unirgy\VendorMinAmounts\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Data
{
    public $cartUpdateActionFlag;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    protected $_priceCurrency;

    public function __construct(
        ScopeConfigInterface $configScopeConfigInterface,
        \Magento\Framework\Pricing\PriceCurrencyInterface  $priceCurrency
    )
    {
        $this->_scopeConfig = $configScopeConfigInterface;
        $this->_priceCurrency = $priceCurrency;
    }

    public function getVendorMinOrderAmount($quote, $vendor, $subtotal)
    {
        $minOrderAmount = $vendor->getMinimumOrderAmount();
        if ($minOrderAmount === null || $minOrderAmount === '') {
            $minOrderAmount = $this->_scopeConfig->getValue('carriers/udropship/minimum_vendor_order_amount', ScopeInterface::SCOPE_STORE, $quote->getStoreId());
        }
        if ($minOrderAmount === null || $minOrderAmount === '') {
            $minOrderAmount = false;
        }
        return $minOrderAmount;
    }

    public function addVendorMinOrderAmountError($quote, $vendor, $minOrderAmount, $subtotal)
    {
        $minOrderAmountFormatted = $this->_priceCurrency->convert($minOrderAmount, $quote->getStore());
        $quoteErr = $this->_scopeConfig->getValue('carriers/udropship/minimum_vendor_order_amount_quote_message', ScopeInterface::SCOPE_STORE, $quote->getStoreId());
        $vendorErr = $this->_scopeConfig->getValue('carriers/udropship/minimum_vendor_order_amount_message', ScopeInterface::SCOPE_STORE, $quote->getStoreId());
        $quote->setHasError(true)->addMessage(
            @sprintf($quoteErr, $vendor->getVendorName(), $minOrderAmountFormatted),
            'udminamountfee'.$vendor->getId()
        );
        $vendorErrors = $quote->getMinVendorOrderAmountErrors();
        if (empty($vendorErrors)) {
            $vendorErrors = [];
        }
        $vendorErrors[$vendor->getId()] = @sprintf($vendorErr, $minOrderAmountFormatted);
        $quote->setMinVendorOrderAmountErrors($vendorErrors);
        return $this;
    }
}