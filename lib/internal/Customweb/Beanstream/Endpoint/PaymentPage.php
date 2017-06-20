<?php

/**
 *  * You are allowed to use this API in your web application.
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

//require_once 'Customweb/Beanstream/Container.php';
//require_once 'Customweb/Beanstream/Authorization/PaymentPage/ParameterBuilder.php';
//require_once 'Customweb/Core/Url.php';
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Beanstream/Endpoint/Abstract.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Endpoint/Annotation/ExtractionMethod.php';
//require_once 'Customweb/Core/Http/Response.php';



/**
 *
 * @author Thomas Hunziker
 * @Controller("ppProcess")
 *
 */
class Customweb_Beanstream_Endpoint_PaymentPage extends Customweb_Beanstream_Endpoint_Abstract {

	private function processPaymentPage(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		$parameters = $request->getParameters();
		if ($transaction->isAuthorizationFailed() || $transaction->isAuthorized()) {
			return new Customweb_Core_Http_Response();
		}
		$transaction->setAuthorizationParameters($parameters);
		if (!$this->verifyHash($transaction, $request)) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The hash verification failed."));
			return new Customweb_Core_Http_Response();
		}
		if (isset($parameters['messageText']) && $parameters['messageText'] != "Approved") {
			$message = Customweb_I18n_Translation::_("The payment failed with an unkown reason");
			if (isset($parameters['messageText'])) {
				$message = Customweb_I18n_Translation::_('The payment failed. Reason:') . $parameters['messageText'];
			}
			$transaction->setAuthorizationFailed($message);
			return new Customweb_Core_Http_Response();
		}
		if (isset($parameters['amount']) && $parameters['amount'] !=
				 Customweb_Util_Currency::formatAmount($transaction->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals(), ".", 2)) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The amount was not the same."));
			return new Customweb_Core_Http_Response();
		}
		if (isset($parameters['trnId'])) {
			$transaction->setPaymentId($parameters['trnId']);
		}
		$parameterBuilder = new Customweb_Beanstream_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getContainer());
		try {
			$transaction->authorize(Customweb_I18n_Translation::__('Customer sucessfully returned from the Beanstream payment page.'));
			$transaction->setAuthorizationParameters($parameters);
			if ($transaction->isDirectCapture()) {
				$transaction->capture();
			}
			return new Customweb_Core_Http_Response();
		}
		catch (Excetpion $e) {
			$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("Error during authorization.") . " " . $e->getMessage());
			return new Customweb_Core_Http_Response();
		}
	}

	/**
	 * @Action("afail")
	 */
	public function processAliasFailedAuthorization(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		$transaction->setAuthorizationFailed(Customweb_I18n_Translation::__("The Alias Manager for PaymentPage must not be used."));
		return Customweb_Core_Http_Response::redirect($transaction->getFailedUrl());
	}

	private function verifyHash(Customweb_Payment_Authorization_ITransaction $transaction, Customweb_Core_Http_IRequest $request){
		$container = new Customweb_Beanstream_Container($this->getContainer());
		$endpointBaseUrl = new Customweb_Core_Url($container->getPaymentPageProcessUrl($transaction->getExternalTransactionId()));
		$hashKey = $container->getConfiguration()->getPaymentPageHashKey();
		$hashValue = null;
		
		$toHash = '';
		
		$sploded = explode('&', $request->getBody());
		foreach ($sploded as $parameter) {
			list($key, $value) = explode('=', $parameter);
			$key = urldecode($key);
			if (array_key_exists($key, $endpointBaseUrl->getQueryAsArray())) {
				continue;
			}
			if ($key == 'hashValue') {
				$hashValue = urldecode($value);
				$toHash = rtrim($toHash, '&');
				$toHash .= $hashKey;
				break;
			}
			$toHash .= $key . '=' . $value . '&';
		}
		
		$computedHash = sha1($toHash);
		return $computedHash == $hashValue;
	}

	/**
	 * @Action("process")
	 */
	public function process(Customweb_Payment_IConfigurationAdapter $configurationAdapter, Customweb_Core_Http_IRequest $request){
		$container = $this->getContainer();
		/* var Customweb_Payment_ITransactionHandler $transactionHandler */
		$transactionHandler = $container->getBean('Customweb_Payment_ITransactionHandler');
		try {
			$idMap = $this->getTransactionId($request);
			if ($idMap['key'] == Customweb_Payment_Endpoint_Annotation_ExtractionMethod::EXTERNAL_TRANSACTION_ID_KEY) {
				$transaction = $transactionHandler->findTransactionByTransactionExternalId($idMap['id']);
			}
			else if ($idMap['key'] == Customweb_Payment_Endpoint_Annotation_ExtractionMethod::PAYMENT_ID_KEY) {
				$transaction = $transactionHandler->findTransactionByPaymentId($idMap['id']);
			}
			if ($transaction === null) {
				throw new Exception('No transaction found');
			}
		}
		catch(Exception $e){
			return new Customweb_Core_Http_Response();
		}
		try {
			$this->processPaymentPage($transaction, $request);
			$transactionHandler->persistTransactionObject($transaction);
		}
		catch (Exception $e) {
			$transactionHandler->persistTransactionObject($transaction);
			
		}
		return new Customweb_Core_Http_Response();
	}
}