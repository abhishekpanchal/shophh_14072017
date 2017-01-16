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
 */

//require_once 'Customweb/Beanstream/Endpoint/Abstract.php';


/**
 * 
 * @author Thomas Hunziker
 * @Controller("aProcess")
 *
 */
class Customweb_Beanstream_Endpoint_Ajax extends Customweb_Beanstream_Endpoint_Abstract {
	
	/**
	 *
	 * @Action("str")
	 */
	public function successTokenReturn(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
	
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->processNormalAuthorization($transaction, $parameters);
		return $response;
	}
	
	/**
	 *
	 * @Action("na")
	 */
	public function makeNewAlias(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
	
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->processInitialAliasAuthorization($transaction, $parameters);
		return $response;
	}
	
	/**
	 *
	 * @Action("pa")
	 */
	public function processExistingAlias(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
	
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->processExistingAlias($transaction, $parameters);
		return $response;
	}
	
	/**
	 *
	 * @Action("ftr")
	 */
	public function failedTokenReturn(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
	
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->failedTokenReturn($transaction, $parameters);
		return $response;
	}
	
	/**
	 *
	 * @Action("tds")
	 */
	public function processThreeDSecure(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request) {
		$adapter = $this->getAdapterFactory()->getAuthorizationAdapterByName($transaction->getAuthorizationMethod());
		$parameters = $request->getParameters();
		$response = $adapter->perform3dSecureTransaction($transaction, $parameters);
		return $response;
	}
	
}