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


class Customweb_Beanstream_Authorization_Ajax_AliasParameterBuilder extends Customweb_Beanstream_Authorization_AbstractParameterBuilder {
	
	
	public function buildInitialAliasParameters(array $parameters) {
		$parameterArray = array(
			'comments'					=> "",
			'language'					=> $this->getLanguage(),
			'billing'					=> $this->getBillingArray(),
			'token'						=> $this->getTokenArray($parameters),
		);
		return json_encode($parameterArray);
	}
	
	private function getBillingArray() {
		$orderContext = $this->getTransaction()->getTransactionContext()->getOrderContext();
		$parameters = array(
			'name'					=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getFirstName() . " " . $orderContext->getBillingAddress()->getLastName(), 0, 64),
			'address_line1'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getStreet(), 0, 64),
			'city'					=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getCity(), 0, 32),
			'country'				=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getCountryIsoCode(), 0, 2),
			'postal_code'			=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getPostCode(), 0, 16),
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
			'name'				=> Customweb_Util_String::substrUtf8($orderContext->getBillingAddress()->getFirstName() . " " . $orderContext->getBillingAddress()->getLastName(), 0, 64),
			'code'				=> $parameters['token']
		);
	
	}
	
	
}