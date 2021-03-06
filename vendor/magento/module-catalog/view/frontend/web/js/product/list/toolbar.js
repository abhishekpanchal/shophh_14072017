/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"

], function($) {
    /**
     * ProductListToolbarForm Widget - this widget is setting cookie and submitting form according to toolbar controls
     */
    $.widget('mage.productListToolbarForm', {

        options: {
            modeControl: '[data-role="mode-switcher"]',
            directionControl: '[data-role="direction-switcher"]',
            orderControl: '[data-role="sorter"]',
            limitControl: '[data-role="limiter"]',
            colorControl: '[data-role="color"]',
            priceControl: '[data-role="price"]',
            catControl: '[data-role="cat"]',
            clearControl: '[data-role="clear"]',
            mode: 'product_list_mode',
            direction: 'product_list_dir',
            order: 'product_list_order',
            limit: 'product_list_limit',
            color: 'color',
            price: 'price',
            cat: 'cat',
            modeDefault: 'grid',
            directionDefault: 'asc',
            orderDefault: 'position',
            limitDefault: '9',
            colorDefault: '',
            priceDefault: '',
            catDefault: '',
            url: ''
        },

        _create: function () {
            this._bind($(this.options.modeControl), this.options.mode, this.options.modeDefault);
            this._bind($(this.options.directionControl), this.options.direction, this.options.directionDefault);
            this._bind($(this.options.orderControl), this.options.order, this.options.orderDefault);
            this._bind($(this.options.limitControl), this.options.limit, this.options.limitDefault);
            this._bind($(this.options.colorControl), this.options.color, this.options.colorDefault);
            this._bind($(this.options.priceControl), this.options.price, this.options.priceDefault);
            this._bind($(this.options.catControl), this.options.cat, this.options.catDefault);
            this._bind($(this.options.clearControl));
        },

        _bind: function (element, paramName, defaultValue) {
            if (element.is("select")) {
                element.on('change', {paramName: paramName, default: defaultValue}, $.proxy(this._processSelect, this));
            } else {
                element.on('click', {paramName: paramName, default: defaultValue}, $.proxy(this._processLink, this));
            }
        },

        _processLink: function (event) {
            event.preventDefault();
            this.changeUrl(
                event.data.paramName,
                $(event.currentTarget).data('value'),
                event.data.default
            );
        },

        _processSelect: function (event) {
            this.changeUrl(
                event.data.paramName,
                event.currentTarget.options[event.currentTarget.selectedIndex].value,
                event.data.default
            );
        },

        changeUrl: function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent;
            var urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters;
            for (var i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;
            if (paramValue == defaultValue) {
                delete paramData[paramName];
            }
            if(paramName == undefined) {
                delete paramData['price'];
                delete paramData['color'];
                delete paramData['cat'];
            }
            if(paramName == 'cat') {
                delete paramData['price'];
            }

            paramData = $.param(paramData);
            location.href = baseUrl + (paramData.length ? '?' + paramData : '');
        }



    });

    return $.mage.productListToolbarForm;
});
