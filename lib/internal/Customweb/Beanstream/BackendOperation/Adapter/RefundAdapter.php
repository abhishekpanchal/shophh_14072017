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

//require_once 'Customweb/Payment/BackendOperation/Adapter/Service/IRefund.php';
//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Util/Invoice.php';
//require_once 'Customweb/Beanstream/AbstractAdapter.php';
//require_once 'Customweb/I18n/Translation.php';



/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Beanstream_BackendOperation_Adapter_RefundAdapter extends Customweb_Beanstream_AbstractAdapter implements 
		Customweb_Payment_BackendOperation_Adapter_Service_IRefund {

	public function refund(Customweb_Payment_Authorization_ITransaction $transaction){
		$items = $transaction->getTransactionContext()->getOrderContext()->getInvoiceItems();
		return partialRefund($transaction, $items, true);
	}

	public function partialRefund(Customweb_Payment_Authorization_ITransaction $transaction, $items, $close){
		$transaction->refundByLineItemsDry($items, $close);
		$amount = Customweb_Util_Currency::formatAmount(Customweb_Util_Invoice::getTotalAmountIncludingTax($items), 
				$transaction->getTransactionContext()->getOrderContext()->getCurrencyCode(), ".", "");
		//if the transaction was directly captured, the initial refund transaction must be made different to a referred capturing
		if (Customweb_Util_Currency::compareAmount($amount, $transaction->getRefundableAmount(), $transaction->getCurrencyCode()) >= 0) {
			$close = true;
		}
		if ($transaction->isDirectCapture()) {
			
			$this->doRefund($transaction->getPaymentId(), $transaction, $amount);
		}
		else {
			$totalNotRefundedAmount = $transaction->getRefundableAmount();
			
			$residualAmount = $amount;
			
			//The logic to refund from different captures
			foreach ($transaction->getCaptures() as $capture) {
				if (!$capture->getFullyRefunded()) {
					// Check if we are finished with refunding
					$amountToRefund = $residualAmount;
					$refundableAmount = $capture->getAmount() - $capture->getRefundedAmount();
					if ($refundableAmount < $residualAmount) {
						$amountToRefund = $refundableAmount;
					}
					
					if ($amountToRefund <= 0) {
						continue;
					}
					
					$residualAmount = $residualAmount - $amountToRefund;
					
					$this->doRefund($capture->getCaptureId(), $transaction, $amountToRefund);
					$capture->addRefundedAmount($amountToRefund);
				}
				if ($residualAmount <= 0) {
					break;
				}
			}
		}
		$transaction->refundByLineItems($items, $close, "The refund was successfully conducted.");
	}

	private function doRefund($captureId, Customweb_Payment_Authorization_ITransaction $transaction, $amount){
		$formattedAmount = Customweb_Util_Currency::formatAmount($amount, $transaction->getCurrencyCode(), '.', '');
		$response = $this->sendRequest($this->getConfiguration()->getBackendApiUrl() . "/v1/payments/" . $captureId . "/returns", 
				json_encode($this->buildParameters($formattedAmount, $transaction)), 
				$this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()), $this->getConfiguration()->getApiAccessPasscode(), "POST");
		
		if (!isset($response['approved']) || $response['approved'] != "1") {
			$code = $response['code'];
			$message = $response['message'];
			throw new Exception(
					Customweb_I18n_Translation::__("The refunding failed with error !code and the message !message.", 
							array(
								'!code' => $code,
								'!message' => $message 
							)));
		}
	}

	private function buildParameters($amount, $transaction){
		return array(
			'amount' => $amount,
			'merchant_id' => $this->getConfiguration()->getMerchantId($transaction->getCurrencyCode()),
			'order_number' => $this->getTransactionAppliedSchema($transaction),
			'ref1' => $transaction->getExternalTransactionId() 
		);
	}
}