/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

'use strict';

/**
 * Define Themes
 *
 * area: area, one of (frontend|adminhtml|doc),
 * name: theme name in format Vendor/theme-name,
 * locale: locale,
 * files: [
 * 'css/styles-m',
 * 'css/styles-l'
 * ],
 * dsl: dynamic stylesheet language (less|sass)
 *
 */
module.exports = {
    shop: {
        area: 'frontend',
        name: 'Shop/hh',
        locale: 'en_US',
        files: [
            'css/bootstrap',
            'css/main'
        ],
        dsl: 'less'
    }
};
