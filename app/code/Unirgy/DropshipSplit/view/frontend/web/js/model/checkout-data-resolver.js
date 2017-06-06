/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define([
    'Magento_Customer/js/model/address-list',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/create-shipping-address',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/create-billing-address',
    'underscore'
], function (
    addressList,
    quote,
    checkoutData,
    createShippingAddress,
    selectShippingAddress,
    selectShippingMethodAction,
    paymentService,
    selectPaymentMethodAction,
    addressConverter,
    selectBillingAddress,
    createBillingAddress,
    _
) {
    'use strict';

    return {
        resolveEstimationAddress: function () {
            if (checkoutData.getShippingAddressFromData()) {
                var address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                selectShippingAddress(address);
            } else {
                this.resolveShippingAddress();
            }
            if (quote.isVirtual()) {
                if  (checkoutData.getBillingAddressFromData()) {
                    address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getBillingAddressFromData());
                    selectBillingAddress(address);
                } else {
                    this.resolveBillingAddress();
                }
            }

        },

        resolveShippingAddress: function () {
            var newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();
            if (newCustomerShippingAddress) {
                createShippingAddress(newCustomerShippingAddress);
            }
            this.applyShippingAddress();
        },

        applyShippingAddress: function (isEstimatedAddress) {
            if (addressList().length == 0) {
                var address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                selectShippingAddress(address);
            }
            var shippingAddress = quote.shippingAddress(),
                isConvertAddress = isEstimatedAddress || false,
                addressData;
            if (!shippingAddress) {
                var isShippingAddressInitialized = addressList.some(function (address) {
                    if (checkoutData.getSelectedShippingAddress() == address.getKey()) {
                        addressData = isConvertAddress
                            ? addressConverter.addressToEstimationAddress(address)
                            : address;
                        selectShippingAddress(addressData);
                        return true;
                    }
                    return false;
                });

                if (!isShippingAddressInitialized) {
                    isShippingAddressInitialized = addressList.some(function (address) {
                        if (address.isDefaultShipping()) {
                            addressData = isConvertAddress
                                ? addressConverter.addressToEstimationAddress(address)
                                : address;
                            selectShippingAddress(addressData);
                            return true;
                        }
                        return false;
                    });
                }
                if (!isShippingAddressInitialized && addressList().length == 1) {
                    addressData = isConvertAddress
                        ? addressConverter.addressToEstimationAddress(addressList()[0])
                        : addressList()[0];
                    selectShippingAddress(addressData);
                }
            }
        },

        resolveShippingRates: function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate();
            var selectedShippingRateByVendor = checkoutData.getSelectedShippingRateByVendor();
            var availableRate = false;
            var availableVendorRates = [];
            var vIds = window.checkoutConfig.udropshipVendorIds;

            if (ratesData.length == 1) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);
                return;
            }

            if (quote.shippingMethod()) {
                if (quote.shippingMethod() && quote.shippingMethod()[0]) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code == quote.shippingMethod()[0].carrier_code
                            && rate.method_code == quote.shippingMethod()[0].method_code
                            && rate.udropship_vendor == 0;
                    });
                }
                _.each(vIds, function (vId) {
                    if (quote.shippingMethod() && quote.shippingMethod()[vId]) {
                        availableVendorRates[vId] = _.find(ratesData, function (rate) {
                            return rate.carrier_code == quote.shippingMethod()[vId].carrier_code
                                && rate.method_code == quote.shippingMethod()[vId].method_code
                                && rate.udropship_vendor == vId;
                        });
                    }
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate.carrier_code + "_" + rate.method_code === selectedShippingRate && rate.udropship_vendor==0;
                });
            }

            _.each(vIds, function (vId) {
                if (!availableVendorRates[vId]
                    && selectedShippingRateByVendor
                    && selectedShippingRateByVendor[vId]
                ) {
                    availableVendorRates[vId] = _.find(ratesData, function (rate) {
                        return rate.carrier_code + "_" + rate.method_code === selectedShippingRateByVendor[vId]
                            && rate.udropship_vendor == vId;
                    });
                }
            });

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = window.checkoutConfig.selectedShippingMethod;
            }

            var cfgMethodByVendor = window.checkoutConfig.selectedShippingMethodByVendor;
            _.each(vIds, function (vId) {
                if (!availableVendorRates[vId]
                    && cfgMethodByVendor
                    && cfgMethodByVendor[vId]
                ) {
                    availableVendorRates[vId] = _.find(ratesData, function (rate) {
                        return rate.carrier_code == cfgMethodByVendor[vId].carrier_code
                            && rate.method_code == cfgMethodByVendor[vId].method_code
                            && rate.udropship_vendor == vId;
                    });
                }
            });

            _.each(vIds, function (vId) {
                if (!availableVendorRates[vId]) {
                    availableVendorRates[vId] = _.find(ratesData, function (rate) {
                        return rate.udropship_default>0
                            && rate.udropship_vendor == vId;
                    });
                }
            });

            availableRate = _.find(ratesData, function(rate) {
                return rate.udropship_vendor==0 && rate.carrier_code == 'udsplit';
            });

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }
            _.each(availableVendorRates, function(smVal, smKey){
                if (_.isObject(smVal) && smVal.udropship_vendor>0) {
                    selectShippingMethodAction(smVal);
                }
            });
        },

        resolvePaymentMethod: function () {
            var availablePaymentMethods = paymentService.getAvailablePaymentMethods();
            var selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();
            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) {
                        selectPaymentMethodAction(payment);
                    }
                });
            }
        },

        resolveBillingAddress: function () {
            var selectedBillingAddress = checkoutData.getSelectedBillingAddress(),
                newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress(),
                shippingAddress = quote.shippingAddress();

            if (selectedBillingAddress) {
                if (selectedBillingAddress == 'new-customer-address' && newCustomerBillingAddressData) {
                    selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                } else {
                    addressList.some(function (address) {
                        if (selectedBillingAddress == address.getKey()) {
                            selectBillingAddress(address);
                        }
                    });
                }
            } else {
                this.applyBillingAddress()
            }
        },
        applyBillingAddress: function () {
            if (quote.billingAddress()) {
                selectBillingAddress(quote.billingAddress());
                return;
            }
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress
                && shippingAddress.canUseForBilling()
                && (shippingAddress.isDefaultShipping() || !quote.isVirtual())) {
                //set billing address same as shipping by default if it is not empty
                selectBillingAddress(quote.shippingAddress());
            }
        }
    }
});
