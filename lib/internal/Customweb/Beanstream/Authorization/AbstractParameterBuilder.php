<?php

//require_once 'Customweb/Util/Currency.php';
//require_once 'Customweb/Beanstream/AbstractParameterBuilder.php';
//require_once 'Customweb/Payment/Authorization/ITransactionContext.php';



/**
 *
 * @author Thomas Brenner
 *
 */
class Customweb_Beanstream_Authorization_AbstractParameterBuilder extends Customweb_Beanstream_AbstractParameterBuilder {

	public function buildAliasChargeParameters($customerCode){
		$paymentInfos = $this->buildPaymentInfoParameter($customerCode);
		$paymentInfos['term_url'] = urlencode($this->getContainer()->getThreeDurl($this->getTransaction()->getExternalTransactionId()));
		return json_encode($paymentInfos);
	}
	
	public function buildRecurringChargeParameters($customerCode) {
		$parameterArray = $this->buildPaymentInfoParameter($customerCode);
		$parameterArray['scEnabled'] = '0';
		$parameterArray['vbvEnabled'] = '0';
		return json_encode($parameterArray);
	}
	
	public function buildPaymentInfoParameter($customerCode) {
		$paymentInfos = array(
			'merchant_id' => $this->getConfiguration()->getMerchantId($this->getTransaction()->getCurrencyCode()),
			'order_number' => $this->getTransactionAppliedSchema($this->getTransaction()),
			'amount' => Customweb_Util_Currency::formatAmount(
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getOrderAmountInDecimals(),
					$this->getTransaction()->getTransactionContext()->getOrderContext()->getCurrencyCode(), '.'),
			'complete' => $this->getPaymentAction(),
			'payment_method' => 'payment_profile',
			'payment_profile' => array(
				'complete' => $this->getPaymentAction(),
				'customer_code' => $customerCode
			)
		);
		return $paymentInfos;
	}

	protected function getLanguage(){
		if ($this->getOrderContext()->getLanguage() == "fr") {
			return "fr";
		}
		else {
			return "en";
		}
	}
	

	protected function getPaymentAction() {
		$transaction = $this->getTransaction();
		if ($transaction->getTransactionContext()->getCapturingMode() === null) {
			$capturingMode = $transaction->getTransactionContext()->getOrderContext()->getPaymentMethod()->getPaymentMethodConfigurationValue('capturing');
			if ($capturingMode == 'authorization') {
				$paymentAction = false;
			}
			else {
				$paymentAction = true;
			}
		}
		else {
			if ($transaction->getTransactionContext()->getCapturingMode() == Customweb_Payment_Authorization_ITransactionContext::CAPTURING_MODE_DEFERRED) {
				$paymentAction = false;
			}
			else {
				$paymentAction = true;
			}
		}
		$transaction->setDirectCapture($paymentAction);
		return $paymentAction;
	}
}