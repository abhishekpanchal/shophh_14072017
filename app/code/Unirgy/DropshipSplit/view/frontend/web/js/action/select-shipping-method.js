/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'Magento_Checkout/js/model/quote'
    ],
    function (quote) {
        "use strict";
        return function (shippingMethod) {
            var shipMethod = quote.shippingMethod();
            shipMethod = shipMethod || {};
            if (shippingMethod) {
                var vId = shippingMethod.udropship_vendor > 0 ? shippingMethod.udropship_vendor : 0;
                shipMethod[vId] = shippingMethod;
                var totals = {
                    amount: 0,
                    base_amount: 0,
                    price_excl_tax: 0,
                    price_incl_tax: 0
                };
                if (shipMethod[0]) {
                    _.each(totals, function (_v, _k) {
                        shipMethod[0][_k] = 0;
                    });
                    _.each(shipMethod, function (__sm) {
                        if (__sm.udropship_vendor > 0) {
                            _.each(totals, function (_v, _k) {
                                totals[_k] += __sm[_k];
                            });
                        }
                    });
                    _.each(totals, function (_v, _k) {
                        shipMethod[0][_k] = _v;
                    });
                }
            }
            quote.shippingMethod(shipMethod)
        }
    }
);
