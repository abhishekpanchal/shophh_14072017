/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Catalog/js/price-utils',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/checkout-data'
    ],
    function (
        ko,
        _,
        Component,
        shippingService,
        priceUtils,
        quote,
        selectShippingMethodAction,
        checkoutData
    ) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Unirgy_DropshipSplit/cart/shipping-rates'
            },
            isVisible: ko.observable(!quote.isVirtual()),
            isLoading: shippingService.isLoading,
            shippingRates: shippingService.getShippingRates(),
            shippingRateGroups: ko.observableArray([]),
            selectedShippingMethod: function (vId) {
                return ko.computed(function () {
                    return quote.shippingMethod() && quote.shippingMethod()[vId]
                        ? vId + '_' + quote.shippingMethod()[vId]['carrier_code'] + '_' + quote.shippingMethod()[vId]['method_code']
                        : null;
                });
            },

            /**
             * @override
             */
            initObservable: function () {
                var self = this;
                this._super();

                this.shippingRates.subscribe(function (rates) {
                    self.shippingRateGroups([]);
                    _.each(rates, function (rate) {
                        var carrierTitle = rate['carrier_title'];

                        if (self.shippingRateGroups.indexOf(rate.udropship_vendor) === -1 && rate.udropship_vendor>0) {
                            self.shippingRateGroups.push(rate.udropship_vendor);
                        }
                    });
                });

                return this;
            },

            getGroupName: function(vId) {
                return window.checkoutConfig.udropshipVendors[vId]
                    ? window.checkoutConfig.udropshipVendors[vId].name
                    : 'UNKNOWN';
            },
            getGroupAddress: function(vId) {
                return window.checkoutConfig.udropshipVendors[vId]
                    ? window.checkoutConfig.udropshipVendors[vId].address
                    : 'UNKNOWN';
            },

            /**
             * Get shipping rates for specific group based on title.
             * @returns Array
             */
            getRatesForGroup: function (vId) {
                return _.filter(this.shippingRates(), function (rate) {
                    return rate.udropship_vendor==vId;
                });
            },

            /**
             * Format shipping price.
             * @returns {String}
             */
            getFormattedPrice: function (price) {
                return priceUtils.formatPrice(price, quote.getPriceFormat());
            },

            /**
             * Set shipping method.
             * @param {String} methodData
             * @returns bool
             */
            selectShippingMethod: function (methodData) {
                selectShippingMethodAction(methodData);
                if (methodData.udropship_vendor>0) {
                    checkoutData.setSelectedShippingRateByVendor(methodData.carrier_code + '_' + methodData.method_code, methodData.udropship_vendor);
                } else if (methodData.udropship_vendor==0) {
                    checkoutData.setSelectedShippingRate(methodData.carrier_code + '_' + methodData.method_code);
                }
                return true;
            }
        });
    }
);
