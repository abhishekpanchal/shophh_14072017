define([
    'jquery',
    'Magento_Ui/js/form/element/select'
], function ($, Select) {
    'use strict';
    return Select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input',
            elementTmpl: 'ui/form/element/select'
        },
        /**
         * Toggle disable state.
         *
         * @param {Number} selected
         */
        toggleDisable: function (selected) {
            this.disabled(!(selected in this.valuesForEnable));
            if (selected in this.valuesForEnable) {
                this.show();
            } else {
                this.hide();
            }
            // if (selected == 2) {
            //
            //     console.log(selected);
            //
            //     // console.log(this.initObservable);
            //     var options = this.options();
            //     delete this.options()[2];
            //     console.log(this.options());
            //
            //     this.observe('options')
            //         .setOptions(this.options());
            // }

        },


    });
});