define([
    'uiComponent',
    'ko',
    'mage/url',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/shipping-service'
], function (Component, ko, urlBuilder, storage, quote, stepNavigator, shippingService) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bluebadger_Dropship/checkout/shipping/summary'
        },
        initialize: function() {
            this.itemList = ko.observableArray([]);
            this.totalQty = ko.observable(0);

            var self = this;
            this._super();

            self.getItems();

            quote.shippingAddress.subscribe(function() {
                self.getItems();
            }, null, 'change');

            quote.totals.subscribe(function() {
                self.getItems();
            }, null, 'change');

            stepNavigator.steps.subscribe(function() {
                self.getItems();
            }, null, 'change');

            shippingService.isLoading.subscribe(function() {
                self.getItems();
            }, null, 'change');
        },

        getItems: function() {
            var self = this;
            var serviceUrl = urlBuilder.build('dropship/ajax/summary');

            return storage.post(
                serviceUrl,
                ''
            ).done(
                function (response) {
                    if (!response.error) {
                        self.itemList([]);
                        for (var i = 0; i < response.quote.vendors.length; i++) {
                            self.itemList.push(response.quote.vendors[i]);
                        }
                        self.totalQty = response.quote.total_qty;
                    } else {
                        alert(response.error);
                    }
                }
            ).fail(
                function (response) {
                    alert (response);
                }
            );
        }
    });
});