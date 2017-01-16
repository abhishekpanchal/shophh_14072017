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

//require_once 'Customweb/Util/String.php';
//require_once 'Customweb/Beanstream/Authorization/AbstractParameterBuilder.php';
//require_once 'Customweb/I18n/Translation.php';


class Customweb_Beanstream_Authorization_Ajax_ParameterBuilder extends Customweb_Beanstream_Authorization_AbstractParameterBuilder {
	
	
	public function buildNormalAuthorizationParameters(array $parameters) {
		$term_url = urlencode($this->getContainer()->getThreeDurl($this->getTransaction()->getExternalTransactionId()));
		if(strlen($term_url) > 256) {
			throw new Exception(
					Customweb_I18n_Translation::__("The 3DSecure term_url parameter is too long (more than 256 characters)."));
		}
		
		$parameterArray = array(
			'merchant_id'				=> $this->getConfiguration()->getMerchantId($this->getTransaction()->getCurrencyCode()),
			'amount'					=> $this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals(),
			'order_number'				=> Customweb_Util_String::substrUtf8($this->getTransactionAppliedSchema($this->getTransaction()), 0 , 30),
			'comments'					=> "",
			'language'					=> $this->getLanguage(),
			'billing'					=> $this->getBillingArray(),
			'payment_method'			=> "token",
			'token'						=> $this->getTokenArray($parameters),
			'term_url'					=> $term_url,
		);
		return json_encode($parameterArray);
	}
	
	public function buildThreeDSecureParameters(array $parameters) {
		$parameterArray = array(
			'payment_method' => "token",
			'card_response' => array(
				'pa_res' => $parameters['PaRes']
			)	
		);
		return json_encode($parameterArray);
	}
	
	private function getBillingArray() {
		$orderContext = $this->getTransaction()->getTransactionContext()->getOrderContext();
		$parameters =  array(
			'name'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getFirstName() . " " . $orderContext->getBillingAddress()->getLastName(), 0, 64),
			'address_line1'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getStreet(), 0, 64),
			'city'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getCity(), 0, 32),
			'country'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getCountryIsoCode(), 0, 2),
			'postal_code'	=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getPostCode(), 0, 16),
			'email_address'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getEMailAddress(), 0, 64),
		);
		if($orderContext->getBillingAddress()->getState() != null) {
			$parameters['province'] = Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getState(), 0, 2);
		}
		if($orderContext->getBillingAddress()->getPhoneNumber() != null) {
			$parameters['phone_number'] = Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getPhoneNumber(), 0, 32);
		}
		return $parameters;
	}
	
	private function getTokenArray($parameters) {
		$orderContext = $this->getTransaction()->getTransactionContext()->getOrderContext();
		return array(
			'complete' 			=> $this->getPaymentAction(),
			'name'				=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getFirstName() . " " . $orderContext->getBillingAddress()->getLastName(), 0, 64),
			'code'				=> $parameters['token'],
			'term_url'			=> $this->getContainer()->getSuccessReturnTokenUrl($this->getTransaction()->getExternalTransactionId())
		);

	}
	
	public function buildAliasForwardingScript() {
		return " function (formFieldValues) {
				window.location.href = '". $this->getContainer()->getExistingAliasUrl($this->getTransaction()->getExternalTransactionId()) ."'
		}";
	}

}