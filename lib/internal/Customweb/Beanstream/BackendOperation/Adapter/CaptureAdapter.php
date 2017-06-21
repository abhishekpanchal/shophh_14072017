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
//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/ICapture.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Beanstream/AbstractAdapter.php';
//require_once 'Customweb/Util/Invoice.php';

//require_once 'Customweb/Beanstream/Authorization/Transaction.php';

/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Beanstream_BackendOperation_Adapter_CaptureAdapter extends Customweb_Beanstream_AbstractAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_ICapture {

	public function capture(Customweb_Payment_Authorization_ITransaction $transaction){
		$items = $transaction->getUncapturedLineItems();
		$this->partialCapture($transaction, $items, true);
	}

	public function partialCapture(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		$amount = Customweb_Util_Invoice::getTotalAmountIncludingTax($items);
		if (Customweb_Util_Currency::compareAmount($amount + $transaction->getCapturedAmount(), $transaction->getAuthorizationAmount(), 
				$transaction->getCurrencyCode()) >= 0) {
			$close = true;
		}
		$transaction->partialCaptureByLineItemsDry($items, $close);
		$captureId = $this->doCapturing($transaction, $amount);
		$item = $transaction->partialCaptureByLineItems($items, $close);
		$item->setCaptureId($captureId);
	}
	
	//------------------------------------------------------------------------------- Assisting Functions --------------------------------------------------------------------------	
	private function doCapturing(Customweb_Payment_Authorization_ITransaction $transaction, $amount){
		$formattedAmount = Customweb_Util_Currency::formatAmount($amount, $transaction->getCurrencyCode(), '.', '');
		$response = $this->sendRequest(
				$this->getConfiguration()->getBackendApiUrl() . "/v1/payments/" . $transaction->getPaymentId() . "/completions", 
				json_encode($this->buildParameters($formattedAmount, $transaction)), 
				$this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscode(), "POST");
		
		if (!isset($response['approved']) || $response['approved'] != "1") {
			$code = $response['code'];
			$message = $response['message'];
			throw new Exception(Customweb_I18n_Translation::__("The capturing failed with error $code and the message $message."));
		}
		
		return $response['id'];
	}

	private function buildParameters($amount, Customweb_Beanstream_Authorization_Transaction $transaction){
		return array(
			'amount' => Customweb_Util_Currency::formatAmount($amount, $transaction->getTransactionContext()->getOrderContext()->getCurrencyCode(), 
					".", ""),
			'merchant_id' => $this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()),
			'order_number' => $this->getTransactionAppliedSchema($transaction),
			'ref1' => $transaction->getExternalTransactionId() 
		);
	}
}