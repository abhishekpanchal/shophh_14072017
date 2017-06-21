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
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Util/String.php';
//require_once 'Customweb/Beanstream/Authorization/AbstractParameterBuilder.php';



/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Beanstream_Authorization_PaymentPage_ParameterBuilder extends Customweb_Beanstream_Authorization_AbstractParameterBuilder {

	public function buildStandardParameters(){
		$parameters = array_merge($this->buildCustomerInformationParameters(), $this->buildTransactionParameters(), $this->buildRedirectUrlParameters());
		return $parameters;
	}
	
	// --------------------------------------------------------------------- HELPING FUNCTIONS --------------------------------------------------------------------- 
	protected function buildCustomerInformationParameters(){
		$address = $this->getTransaction()->getTransactionContext()->getOrderContext()->getBillingAddress();
		$parameters = array(
			'ordName' => Customweb_Util_String::substrUtf8($address->getFirstName() . " " . $address->getLastName(), 0, 64),
			'ordEmailAddress' => Customweb_Util_String::substrUtf8($address->getEMailAddress(), 0, 64),
			'ordPhoneNumber' => Customweb_Util_String::substrUtf8($address->getPhoneNumber(), 0, 32),
			'ordAddress1' => Customweb_Util_String::substrUtf8($address->getStreet(), 0, 32),
			'ordCity' => Customweb_Util_String::substrUtf8($address->getCity(), 0, 32),
			'ordPostalCode' => Customweb_Util_String::substrUtf8($address->getPostCode(), 0, 32),
			'ordCountry' => Customweb_Util_String::substrUtf8($address->getCountryIsoCode(), 0, 2) 
		);
		if($address->getState() != null) {
			$parameters['ordProvince'] = Customweb_Util_String::substrUtf8($address->getState(), 0, 2); 
		}
		return $parameters;
	}

	protected function buildTransactionParameters(){
		return array(
			'merchant_id' => $this->getConfiguration()->getMerchantId($this->getTransaction()->getCurrencyCode()),
			'trnAmount' => Customweb_Util_Currency::formatAmount(
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals(), 
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getCurrencyCode(), '.'),
			'trnOrderNumber' => $this->getTransactionAppliedSchema($this->getTransaction()),
			'trnType' => $this->formTransactionType(),
			'ref1' => $this->getTransaction()->getExternalTransactionId(),
		);
	}

	protected function buildRedirectUrlParameters(){
		return array(
			//'approvedPage' => $this->getContainer()->getPaymentPageProcessUrl($this->getTransaction()->getExternalTransactionId()),
			'approvedPage' => $this->getTransaction()->getSuccessUrl(),
			//'declinedPage' => $this->getContainer()->getPaymentPageProcessUrl($this->getTransaction()->getExternalTransactionId()),
			'declinedPage' => $this->getTransaction()->getFailedUrl() 
		);
	}

	public function formTransactionType(){
		$direct = $this->getPaymentAction();
		if($direct) {
			return 'P';
		}
		else {
			return 'PA';
		}
	}
	
	
}





