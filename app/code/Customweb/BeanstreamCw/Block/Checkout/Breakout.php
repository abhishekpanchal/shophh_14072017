<?php
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

namespace Customweb\BeanstreamCw\Block\Checkout;

class Breakout extends AbstractCheckout
{
	/**
	 * @var string
	 */
	protected $_template = 'Customweb_BeanstreamCw::checkout/breakout.phtml';

	/**
	 * @return string
	 */
	public function getRedirectionUrl()
	{
		$transactionContext = $this->getTransaction()->getTransactionObject()->getTransactionContext();
		if ($this->getTransaction()->getTransactionObject()->isAuthorizationFailed()) {
			return \Customweb_Util_Url::appendParameters(
					$transactionContext->getFailedUrl(),
					$transactionContext->getCustomParameters()
			);
		} else {
			return \Customweb_Util_Url::appendParameters(
					$transactionContext->getSuccessUrl(),
					$transactionContext->getCustomParameters()
			);
		}
	}
}