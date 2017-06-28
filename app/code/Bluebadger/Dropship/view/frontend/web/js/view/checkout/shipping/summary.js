define([
    'uiComponent',
    'ko',
    'mage/url',
    'mage/storage'
], function (Component, ko, urlBuilder, storage) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bluebadger_Dropship/checkout/shipping/summary'
        },
        totalQty: '',
        itemList: ko.observableArray([]),

        initialize: function() {
            this._super();
            var self = this;
            self.getItems();
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
                        self.itemList = response.quote.vendors;
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