define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/udsplit-validation',
        '../model/udsplit-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        'use strict';
        defaultShippingRatesValidator.registerValidator('udsplit', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('udsplit', shippingRatesValidationRules);
        return Component;
    }
);
