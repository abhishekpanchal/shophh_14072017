define([
    'uiComponent',
    'ko',
    'mage/url',
    'mage/storage',
    'Magento_Checkout/js/model/quote'
], function (Component, ko, urlBuilder, storage, quote) {
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

            quote.shippingAddress.subscribe(function() {
                self.getItems();
                console.log('re-rendering template');
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
                        console.log(response.quote.vendors.length);
                        if (!response.quote.vendors.length) {
                            window.location.reload();
                        } else {
                            self.itemList = response.quote.vendors;
                            self.totalQty = response.quote.total_qty;
                        }
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