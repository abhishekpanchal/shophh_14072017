define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal',
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({
        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var gift_value = uiRegistry.get('index = gift_value');
            var gift_from = uiRegistry.get('index = gift_from');
            var gift_to = uiRegistry.get('index = gift_to');
            var gift_dropdown = uiRegistry.get('index = gift_dropdown');
            var gift_price = uiRegistry.get('index = gift_price');
            var gift_price_type = uiRegistry.get('index = gift_price_type');
            if (value == 1) {
                jQuery("select[name='product[gift_price_type]'] option[value='2']").show();
                gift_value.enable();
                gift_from.disable();
                gift_to.disable();
                gift_dropdown.disable();
                gift_value.show();
                gift_from.hide();
                gift_to.hide();
                gift_dropdown.hide();
            } else if (value == 2) {
                gift_value.disable();
                gift_from.enable();
                gift_to.enable();
                gift_dropdown.disable();
                gift_value.hide();
                gift_from.show();
                gift_to.show();
                gift_dropdown.hide();
                jQuery("select[name='product[gift_price_type]'] option[value='2']").hide();
                if (jQuery("select[name='product[gift_price_type]']").val() == 1) {
                    jQuery("input[name='product[gift_price]']").parent('div').parent('div').hide();
                }
                if(jQuery("select[name='product[gift_price_type]']").val() == 2) {
                    jQuery("select[name='product[gift_price_type]'] option[value='3']").attr("selected","selected");
                    jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Percentage');
                    jQuery("input[name='product[gift_price]']").parent('div').parent('div').show();
                    jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter percentage(s) of Gift Card value(s) to calculate Gift Card price(s). For example: Type of Gift Card value: Dropdown values Gift Card values: 20,30,40 Percentage: 90,90,90 So customers only have to pay 90% of Gift Card value, $36 for a $40 Gift card for instance.');
                    jQuery("input[name='product[gift_price]']").val(100).trigger("change");
                } else {

                }
            } else {
                jQuery("select[name='product[gift_price_type]'] option[value='2']").show();
                gift_value.disable();
                gift_from.disable();
                gift_to.disable();
                gift_dropdown.enable();
                gift_value.hide();
                gift_from.hide();
                gift_to.hide();
                gift_dropdown.show();
            }
            return this._super();
        },
        initialize: function () {
            // console.log(this.getSelected);
            var gift_value = uiRegistry.get('index = gift_value');
            var gift_from = uiRegistry.get('index = gift_from');
            var gift_to = uiRegistry.get('index = gift_to');
            var gift_dropdown = uiRegistry.get('index = gift_dropdown');
            var gift_price = uiRegistry.get('index = gift_price');

            var value = this._super().getInitialValue();
            if (value == 1) {
                jQuery("select[name='product[gift_price_type]'] option[value='2']").show();
                gift_value.enable();
                gift_from.disable();
                gift_to.disable();
                gift_dropdown.disable();
                gift_value.show();
                gift_from.hide();
                gift_to.hide();
                gift_dropdown.hide();

            } else if (value == 2) {
                jQuery("select[name='product[gift_price_type]'] option[value='2']").hide();
                gift_value.disable();
                gift_from.enable();
                gift_to.enable();
                gift_dropdown.disable();
                gift_value.hide();
                gift_from.show();
                gift_to.show();
                gift_dropdown.hide();

                if (jQuery("select[name='product[gift_price_type]']").val() == 1) {
                    jQuery("input[name='product[gift_price]']").parent('div').parent('div').hide();
                }
                if(jQuery("select[name='product[gift_price_type]']").val() == 2) {
                    console.log('22');
                    jQuery("select[name='product[gift_price_type]'] option[value='3']").attr("selected","selected");
                    jQuery("input[name='product[gift_price]']").parent('div').prev().find('span').text('Percentage');
                    jQuery("input[name='product[gift_price]']").parent('div').parent('div').show();
                    jQuery("input[name='product[gift_price]']").parent('div').find('span').text('Enter percentage(s) of Gift Card value(s) to calculate Gift Card price(s). For example: Type of Gift Card value: Dropdown values Gift Card values: 20,30,40 Percentage: 90,90,90 So customers only have to pay 90% of Gift Card value, $36 for a $40 Gift card for instance.');
                    jQuery("input[name='product[gift_price]']").val(100).trigger("change");
                }
            } else {
                jQuery("select[name='product[gift_price_type]'] option[value='2']").show();
                gift_value.disable();
                gift_from.disable();
                gift_to.disable();
                gift_dropdown.enable();
                gift_value.hide();
                gift_from.hide();
                gift_to.hide();
                gift_dropdown.show();
            }
            return this;
                // ._super()
                // .changeTypeUpload(this.initialValue);

        },
    });
});