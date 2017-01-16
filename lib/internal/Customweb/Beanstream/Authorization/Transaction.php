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

//require_once 'Customweb/Payment/Authorization/DefaultTransaction.php';
//require_once 'Customweb/Beanstream/Authorization/TransactionCapture.php';

class Customweb_Beanstream_Authorization_Transaction extends Customweb_Payment_Authorization_DefaultTransaction {
	private $expiryMonth = null;
	private $expiryYear = null;
	private $customerCode = null;
	private $directCapture = true;

	public function getExpiryMonth(){
		return $this->expiryMonth;
	}

	public function setExpiryMonth($expiryMonth){
		$this->expiryMonth = $expiryMonth;
		return $this;
	}

	public function getExpiryYear(){
		return $this->expiryYear;
	}

	public function setExpiryYear($expiryYear){
		$this->expiryYear = $expiryYear;
		return $this;
	}

	public function setCustomerCode($customerCode){
		$this->customerCode = $customerCode;
		return $this;
	}
	
	public function getCustomerCode() {
		return $this->customerCode;
	}

	public function isDirectCapture() {
		return $this->directCapture;
	}
	
	public function setDirectCapture($direct) {
		$this->directCapture = $direct;
		return $this;
	}

	protected function buildNewCaptureObject($captureId, $amount, $status = NULL){
		return new Customweb_Beanstream_Authorization_TransactionCapture($captureId, $amount, $status, $this->getCurrencyCode());
	}


}