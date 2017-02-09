define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        // afterRender: function () {
        //     console.log(this.value());
        // },
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var gift_price = uiRegistry.get('index = gift_price');
            if (value == 1) {
                gift_price.hide();
                gift_price.disable();
            } else if (value == 2) {
                gift_price.show();
                gift_price.enable();
                jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Gift card price');
                jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter fixed price(s) corresponding to Gift Card value(s).For example: Type of Gift Card value: Dropdown values; Gift Card values : 20,30,40; Gift Card price: 15,25,35; So customers only have to pay $25 for a $30 Gift card.');
                jQuery("input[name='product[gift_price]']").val('');
            } else {
                gift_price.show();
                gift_price.enable();
                jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Percentage');
                jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter percentage(s) of Gift Card value(s) to calculate Gift Card price(s). For example: Type of Gift Card value: Dropdown values Gift Card values: 20,30,40 Percentage: 90,90,90 So customers only have to pay 90% of Gift Card value, $36 for a $40 Gift card for instance.');
                jQuery("input[name='product[gift_price]']").val(100).trigger("change");
            }
            return this._super();
        },
        initialize: function () {
            var gift_price = uiRegistry.get('index = gift_price');
            // console.log(gift_price._get_children());
            var value = this._super().getInitialValue();
            if (value == 1) {
                gift_price.hide();
                gift_price.disable();
            } else if (value == 2) {
                gift_price.show();
                gift_price.enable();
                jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Gift card price');
                jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter fixed price(s) corresponding to Gift Card value(s).For example: Type of Gift Card value: Dropdown values; Gift Card values : 20,30,40; Gift Card price: 15,25,35; So customers only have to pay $25 for a $30 Gift card.');
                jQuery("input[name='product[gift_price]']").val('');
            } else {
                gift_price.show();
                gift_price.enable();
                jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Percentage');
                jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter percentage(s) of Gift Card value(s) to calculate Gift Card price(s). For example: Type of Gift Card value: Dropdown values Gift Card values: 20,30,40 Percentage: 90,90,90 So customers only have to pay 90% of Gift Card value, $36 for a $40 Gift card for instance.');
                jQuery("input[name='product[gift_price]']").val(100).trigger("change");
            }
            return this;
        }
    });
});