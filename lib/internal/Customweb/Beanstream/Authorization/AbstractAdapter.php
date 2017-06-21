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

//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Beanstream/AbstractAdapter.php';



/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Beanstream_Authorization_AbstractAdapter extends Customweb_Beanstream_AbstractAdapter {

	public function validate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext, array $formData){}

	public function sendAliasRequest(Customweb_Beanstream_Authorization_Transaction $transaction, $parameterString){
		$paymentInfos = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/payments", $parameterString, 
				$this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscode(), "POST");
		
		$transaction->setAuthorizationParameters($paymentInfos);
		return $paymentInfos;
	}

	protected function handleAliasAuthorization(Customweb_Beanstream_Authorization_Transaction $transaction, array $paymentInfos){
		if (!isset($paymentInfos['approved']) || $paymentInfos['approved'] != "1") {
			$reason = Customweb_I18n_Translation::__('Charging Alias: Unkown error');
			if (isset($paymentInfos['message'])) {
				$reason = $paymentInfos['message'];
			}
			throw new Exception(Customweb_I18n_Translation::__("The alias payment failed.") . " " . $reason);
		}
		
		if (isset($paymentInfos['id'])) {
			$transaction->setPaymentId($paymentInfos['id']);
		}
		
		$transaction->authorize();
		if (isset($paymentInfos['type']) && $paymentInfos['type'] == "P" && $transaction->isDirectCapture()) {
			$transaction->capture();
		}
	}
}