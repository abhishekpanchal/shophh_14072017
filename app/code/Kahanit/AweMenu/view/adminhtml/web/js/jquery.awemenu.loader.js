/**
 * Awe Menu is quick, easy to setup and WYSIWYG menu management system
 *
 * Awe Menu by Kahanit(https://www.kahanit.com) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.1.0
 */

var Handlebars;

define([
    'jquery',
    'jquery/ui',
    'Kahanit_AweMenu_handlebars',
    'Kahanit_AweMenu_bootstrap',
    'Kahanit_AweMenu_jquery_datatables',
    'Kahanit_AweMenu_datatables_bootstrap',
    'Kahanit_AweMenu_jquery_fancytree',
    'Kahanit_AweMenu_bootstrap_colorpicker',
    'Kahanit_AweMenu_fontawesome_iconpicker',
    'Kahanit_AweMenu_ace',
    'tinymce',
    'Kahanit_AweMenu_googlemaps',
    'Kahanit_AweMenu_jquery_awemenu'
], function ($) {
    'use strict';

    $.widget("mage.aweMenuLoader", {
        options: {
            url: '',
            jsUrl: '',
            langs: '',
            activeLang: '',
            entities: ''
        },
        _create: function () {
            Handlebars = require('Kahanit_AweMenu_handlebars');

            $('#am-builder').awemenu({
                'url': this.options.url,
                'jsUrl': this.options.jsUrl,
                'langs': this.options.langs,
                'activeLang': this.options.activeLang,
                'entities': this.options.entities
            });
        }
    });

    return $.mage.aweMenuLoader;
});