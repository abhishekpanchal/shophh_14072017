/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_BeanstreamCw
 * 
 */
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
        	
			{
			    type: 'beanstreamcw_creditcard',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_creditcard-method'
			},
			{
			    type: 'beanstreamcw_visa',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_visa-method'
			},
			{
			    type: 'beanstreamcw_mastercard',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_mastercard-method'
			},
			{
			    type: 'beanstreamcw_americanexpress',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_americanexpress-method'
			},
			{
			    type: 'beanstreamcw_diners',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_diners-method'
			},
			{
			    type: 'beanstreamcw_jcb',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_jcb-method'
			},
			{
			    type: 'beanstreamcw_discovercard',
			    component: 'Customweb_BeanstreamCw/js/view/payment/method-renderer/beanstreamcw_discovercard-method'
			}
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);