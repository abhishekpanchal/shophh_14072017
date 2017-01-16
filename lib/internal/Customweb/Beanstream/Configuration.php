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



/**
 *
 * @author thomas
 * @Bean
 */
class Customweb_Beanstream_Configuration {
	
	/**
	 *         	 								 	  
	 *
	 * @var Customweb_Payment_IConfigurationAdapter
	 */
	private $configurationAdapter = null;

	public function __construct(Customweb_Payment_IConfigurationAdapter $configurationAdapter){
		$this->configurationAdapter = $configurationAdapter;
	}

	public function getConfigurationAdapter(){
		return $this->configurationAdapter;
	}

	/**
	 * Returns whether the gateway is in test mode or in live mode.
	 *         	 								 	  
	 *
	 * @return boolean True if the system is in live mode. Else return false.
	 */
	public function isTestMode(){
		return $this->getConfigurationAdapter()->getConfigurationValue('operation_mode') == 'test';
	}

	public function getBackendApiUrl(){
		return "https://beanstream.com/api";
	}

	public function getAjaxUrl(){
		return "https://www.beanstream.com/scripts/tokenization/legato-1.1.min.js";
	}

	public function getPaymentPageUrl(){
		return "https://www.beanstream.com/scripts/payment/payment.asp";
	}

	public function getApiAccessPasscode(){
		if ($this->isTestMode()) {
			$apiAccessPasscode = $this->getConfigurationAdapter()->getConfigurationValue('api_access_passcode_no_alias_test');
		}
		else {
			$apiAccessPasscode = $this->getConfigurationAdapter()->getConfigurationValue('api_access_passcode_no_alias_live');
		}
		if (empty($apiAccessPasscode)) {
			throw new Exception(Customweb_I18n_Translation::__("The API Access Passcode field is empty."));
		}
		return $apiAccessPasscode;
	}

	public function getApiAccessPasscodeSecurePaymentProfile(){
		if ($this->isTestMode()) {
			$apiAccessPasscodeAlias = $this->getConfigurationAdapter()->getConfigurationValue('api_access_passcode_alias_test');
		}
		else {
			$apiAccessPasscodeAlias = $this->getConfigurationAdapter()->getConfigurationValue('api_access_passcode_alias_live');
		}
		if (empty($apiAccessPasscodeAlias)) {
			throw new Exception(Customweb_I18n_Translation::__("The API Access Passcode (Alias) field is empty."));
		}
		return $apiAccessPasscodeAlias;
	}

	public function getMerchantId($currency){
		$currency = strtolower($currency);
		if ($this->isTestMode()) {
			if($this->getConfigurationAdapter()->existsConfiguration('merchant_id_' . $currency . '_test')){
				$merchantId = $this->getConfigurationAdapter()->getConfigurationValue('merchant_id_' . $currency . '_test');
			}
			else{
				throw new Exception(Customweb_I18n_Translation::__("The currency @currency is not supported.", 
							array(
								"@currency" => strtoupper($currency) 
							)));
			}
		}
		else {
			if($this->getConfigurationAdapter()->existsConfiguration('merchant_id_' . $currency . '_live')){
				$merchantId = $this->getConfigurationAdapter()->getConfigurationValue('merchant_id_' . $currency . '_live');
			}
			else{
				throw new Exception(Customweb_I18n_Translation::__("The currency @currency is not supported.",
						array(
							"@currency" => strtoupper($currency)
						)));
			}
		}
		if (empty($merchantId)) {
			throw new Exception(
					Customweb_I18n_Translation::__("The merchant id field for currency @currency is empty.", 
							array(
								"@currency" => strtoupper($currency) 
							)));
		}
		return $merchantId;
	}

	public function getOrderIdSchema(){
		return $this->getConfigurationAdapter()->getConfigurationValue('order_id_schema');
	}

	public function getPaymentPageHashKey(){
		if ($this->isTestMode()) {
			$hashKey = $this->getConfigurationAdapter()->getConfigurationValue('hash_key_test');
		}
		else {
			$hashKey = $this->getConfigurationAdapter()->getConfigurationValue('hash_key_live');
		}
		if (empty($hashKey)) {
			throw new Exception(Customweb_I18n_Translation::__("The hash key field is empty."));
		}
		return $hashKey;
	}

}
