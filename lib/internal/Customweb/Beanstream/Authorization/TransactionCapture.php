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

//require_once 'Customweb/Payment/Authorization/DefaultTransactionCapture.php';
//require_once 'Customweb/Util/Currency.php';



/**
 *
 * @author Thomas Brenner
 * 
 *
 */
class Customweb_Beanstream_Authorization_TransactionCapture extends Customweb_Payment_Authorization_DefaultTransactionCapture {
	private $currencyCode = null;
	private $refundedAmount = 0;
	private $fullyRefunded = false;

	public function __construct($captureId, $amount, $status = NULL, $currencyCode) {
		parent::__construct($captureId, $amount, $status);
		$this->currencyCode = $currencyCode;
	}
	
	public function getRefundedAmount(){
		return $this->refundedAmount;
	}
/*
	public function setRefundedAmount($refundedAmount){
		$this->refundedAmount = $refundedAmount;
		return $this;
	}
*/
	public function addRefundedAmount($addAmount) {
		$this->refundedAmount = $this->refundedAmount + $addAmount;
		if(Customweb_Util_Currency::compareAmount($this->refundedAmount, $this->getAmount(), $this->currencyCode) >= 0) {
			$this->setFullyRefunded(true);
		}
	}
	
	public function getFullyRefunded(){
		return $this->fullyRefunded;
	}

	private function setFullyRefunded($fullyRefunded){
		$this->fullyRefunded = $fullyRefunded;
		return $this;
	}
	
	
	
	
}