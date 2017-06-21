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

//require_once 'Customweb/Beanstream/Authorization/Transaction.php';
//require_once 'Customweb/Beanstream/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Beanstream/Authorization/Ajax/AliasParameterBuilder.php';
//require_once 'Customweb/Beanstream/Authorization/Ajax/ParameterBuilder.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Payment/Authorization/Ajax/IAdapter.php';
//require_once 'Customweb/Util/JavaScript.php';



/**
 * @Bean
 */
class Customweb_Beanstream_Authorization_Ajax_Adapter extends Customweb_Beanstream_Authorization_AbstractAdapter implements
		Customweb_Payment_Authorization_Ajax_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function getAdapterPriority(){
		return 250;
	}

	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
	}

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$paymentMethod = $this->getContainer()->getBean('Customweb_Beanstream_Method_Factory')->getPaymentMethod(
				$orderContext->getPaymentMethod(), self::AUTHORIZATION_METHOD_NAME);
		$paymentMethod->preValidate($orderContext, $paymentContext);
	}

	public function createTransaction(Customweb_Payment_Authorization_Ajax_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Beanstream_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}

	public function getAjaxFileUrl(Customweb_Payment_Authorization_ITransaction $transaction){
		return $this->getConfiguration()->getAjaxUrl();
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}

	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $paymentCustomerContext){
		$method = $this->getContainer()->getBean('Customweb_Beanstream_Method_Factory')->getPaymentMethod($orderContext->getPaymentMethod(),
				self::AUTHORIZATION_METHOD_NAME);
		return $method->getFormFields($orderContext, $aliasTransaction, $failedTransaction, self::AUTHORIZATION_METHOD_NAME, false);
	}

	public function getJavaScriptCallbackFunction(Customweb_Payment_Authorization_ITransaction $transaction){
		if ($transaction->getTransactionContext()->getAlias() != "new" && $transaction->getTransactionContext()->getAlias() != null) {
			$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_ParameterBuilder($transaction, $this->getContainer());
			return $parameterBuilder->buildAliasForwardingScript();
		}
		else if ($transaction->getTransactionContext()->getAlias() == "new" || $transaction->getTransactionContext()->createRecurringAlias()) {
			$url = $this->getContainer()->getNewAliasUrl($transaction->getExternalTransactionId());
		}
		else {
			$url = $this->getContainer()->getSuccessReturnTokenUrl($transaction->getExternalTransactionId());
		}

		$successCallbackFunction = $transaction->getTransactionContext()->getJavaScriptSuccessCallbackFunction();
		$failedCallbackFunction = $transaction->getTransactionContext()->getJavaScriptFailedCallbackFunction();

		//	$js = "function () {";
		$formBuild = "
				var successFunction = " . $successCallbackFunction . ";
				var failedFunction = " . $failedCallbackFunction . ";
				var tokenSuccessUrl = '" . $url . "';
				var tokenFailedUrl = '" . $this->getContainer()->getFailedReturnTokenUrl($transaction->getExternalTransactionId()) . "';

				var formFieldValues = beanstreamFormFields;
				var year = formFieldValues.expiry_year.substr(2,4);

				var f = document.createElement(\"form\");
					f.setAttribute('method',\"post\");
					f.setAttribute('action', tokenSuccessUrl );
					f.setAttribute('id',\"frmPayment\");
					f.setAttribute('style', \"visibility:hidden;\");

				var i1 = document.createElement(\"input\");
					i1.setAttribute('type',\"text\");
					i1.setAttribute('id',\"trnCardNumber\");
					i1.setAttribute('value', formFieldValues.card_number);

				var i2 = document.createElement(\"input\");
					i2.setAttribute('type',\"text\");
					i2.setAttribute('id',\"trnCardCvd\");
					i2.setAttribute('value', formFieldValues.card_cvn);

				var i3 = document.createElement(\"input\");
					i3.setAttribute('type',\"text\");
					i3.setAttribute('id',\"trnExpMonth\");
					i3.setAttribute('value', formFieldValues.expiry_month);

				var i4 = document.createElement(\"input\");
					i4.setAttribute('type',\"text\");
					i4.setAttribute('id',\"trnExpYear\");
					i4.setAttribute('value', year);

				var s = document.createElement(\"input\");
					s.setAttribute('type',\"submit\");
					s.setAttribute('id',\"submitButton\");

				f.appendChild(i1);
				f.appendChild(i2);
				f.appendChild(i3);
				f.appendChild(i4);
				f.appendChild(s);
				document.getElementsByTagName('body')[0].appendChild(f);
				if(typeof window.jQuery == 'undefined') {
					window.jQuery = cwjQuery;
				}


				getLegato( function(legato) {
						if (legato.success) {
							successFunction(tokenSuccessUrl + '&token=' + legato.token);
                       	} else {
                           	failedFunction(tokenFailedUrl + '&message=' + legato.message);
                      	}
					}
				);";

		$complete = "function(formFieldValues) { var beanstreamFormFields = formFieldValues; if(typeof window.jQuery == 'undefined') {" .
				 Customweb_Util_JavaScript::getLoadJQueryCode(null, 'cwjQuery', 'function(){' . $formBuild . '}') . '} else {' . $formBuild . '}}';
		return $complete;
	}

	/**
	 * This function handles the normal process (without alias manager) of a successful token generation, 3D-security is alsoo handled in this
	 * function.
	 *
	 * @param Customweb_Beanstream_Authorization_Transaction $transaction
	 * @param unknown $parameters
	 * @throws Exception
	 * @return string
	 */
	public function processNormalAuthorization(Customweb_Beanstream_Authorization_Transaction $transaction, $parameters){
		$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_ParameterBuilder($transaction, $this->getContainer());

		try {
			$currency = strtoupper($transaction->getCurrencyCode());
			$paymentInfos = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/payments",
					$parameterBuilder->buildNormalAuthorizationParameters($parameters),
					$this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscode(),
					"POST");

			if (isset($paymentInfos['merchant_data']) && isset($paymentInfos['contents'])) {
				return urldecode($paymentInfos['contents']);
			}

			$this->finalizeNormalAuthorization($transaction, $paymentInfos);
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		return $this->finalizeRequest($transaction);
	}

	/**
	 * This function handles the process of the initial alias/recurring transaction (creates a profil on beanstream).
	 *
	 * @param Customweb_Beanstream_Authorization_Transaction $transaction
	 * @param array $parameters
	 * @throws Exception
	 * @return string
	 */
	public function processInitialAliasAuthorization(Customweb_Beanstream_Authorization_Transaction $transaction, array $parameters){
		$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_AliasParameterBuilder($transaction, $this->getContainer());
		try {
			$response = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/profiles",
					$parameterBuilder->buildInitialAliasParameters($parameters), $this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscodeSecurePaymentProfile(),
					"POST");

			if (!isset($response['code']) || $response['code'] != "1") {
				$message = Customweb_I18n_Translation::__('Initiate Alias: Unkown error');
				if (isset($response['message'])) {
					$message = $response['message'];
				}
				else if (isset($response['code'])) {
					$message = 'Error: ' . $response['code'];
				}
				throw new Exception(Customweb_I18n_Translation::__('The customer profile could not be established:') . ' ' . $message);
			}

			$transaction->setCustomerCode($response['customer_code']);

			$cardInfos = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/profiles/" . $transaction->getCustomerCode(),
					array(), $this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscodeSecurePaymentProfile(), 'GET');
			if (!isset($cardInfos['code']) || $cardInfos['code'] != "1") {
				$message = Customweb_I18n_Translation::__('Retrieving CardData: Unkown error');
				if (isset($cardInfos['message'])) {
					$message = $cardInfos['message'];
				}
				else if (isset($cardInfos['code'])) {
					$message = 'Error: ' . $cardInfos['code'];
				}
				throw new Exception(Customweb_I18n_Translation::__('Alias information retrievel failed with error code:') . ' ' . $message);
			}
			$result = $this->sendAliasRequest($transaction, $parameterBuilder->buildAliasChargeParameters($transaction->getCustomerCode()));

			$transaction->setAliasForDisplay($cardInfos['card']['number']);
			$transaction->setExpiryMonth($cardInfos['card']['expiry_month']);
			$transaction->setExpiryYear($cardInfos['card']['expiry_year']);

			if (isset($result['merchant_data']) && isset($result['contents'])) {
				return urldecode($result['contents']);
			}
			else {
				$this->handleAliasAuthorization($transaction, $result);
			}
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}

		return $this->finalizeRequest($transaction);
	}

	/**
	 * This function processes existing alias (similar to recurring adapter).
	 *
	 * @param Customweb_Beanstream_Authorization_Transaction $transaction
	 * @param array $parameters
	 * @return string
	 */
	public function processExistingAlias(Customweb_Beanstream_Authorization_Transaction $transaction, array $parameters){
		$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_AliasParameterBuilder($transaction, $this->getContainer());
		$customerCode = $transaction->getTransactionContext()->getAlias()->getCustomerCode();
		try {
			$paymentInfos = $this->sendAliasRequest($transaction, $parameterBuilder->buildAliasChargeParameters($customerCode));
			if (isset($paymentInfos['merchant_data']) && isset($paymentInfos['contents'])) {
				return urldecode($paymentInfos['contents']);
			}
			else {
				$this->handleAliasAuthorization($transaction, $paymentInfos);
			}
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		if ($transaction->getTransactionContext()->createRecurringAlias()) {
			$transaction->setCustomerCode($customerCode);
		}
		return $this->finalizeRequest($transaction);
	}

	private function finalizeRequest(Customweb_Beanstream_Authorization_Transaction $transaction){
		if ($transaction->isAuthorizationFailed()) {
			return "redirect:" . $transaction->getFailedUrl();
		}
		else {
			return "redirect:" . $transaction->getSuccessUrl();
		}
	}

	private function finalizeNormalAuthorization(Customweb_Beanstream_Authorization_Transaction $transaction, array $paymentInfos){
		if (isset($paymentInfos['approved']) && $paymentInfos['approved'] != "1") {
			throw new Exception(Customweb_I18n_Translation::__("The payment failed."));
		}

		if (isset($paymentInfos['message']) && $paymentInfos['message'] != "Approved") {
			throw new Exception(Customweb_I18n_Translation::__("The payment failed."));
		}

		if (isset($paymentInfos['id'])) {
			$transaction->setPaymentId($paymentInfos['id']);
		}

		$transaction->authorize();
		if (isset($paymentInfos['type']) && $paymentInfos['type'] == "P" && $transaction->isDirectCapture()) {
			$transaction->capture();
		}
	}

	/**
	 * endpoint for 3D redirection function
	 *
	 * @param Customweb_Beanstream_Authorization_Transaction $transaction
	 * @param unknown $parameters
	 * @return string
	 */
	public function perform3dSecureTransaction(Customweb_Beanstream_Authorization_Transaction $transaction, $parameters){
		$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_ParameterBuilder($transaction, $this->getContainer());
		try {
			$paymentInfos = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/payments/" . $parameters['MD'] . "/continue",
					$parameterBuilder->buildThreeDSecureParameters($parameters), $this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscode(),
					"POST");

			$this->finalizeNormalAuthorization($transaction, $paymentInfos);
		}
		catch (Exception $e) {
			$transaction->setAuthorizationFailed($e->getMessage());
		}
		return $this->finalizeRequest($transaction);
	}
}
