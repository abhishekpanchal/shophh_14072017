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
//require_once 'Customweb/Beanstream/Authorization/Transaction.php';
//require_once 'Customweb/Beanstream/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Beanstream/Authorization/PaymentPage/ParameterBuilder.php';
//require_once 'Customweb/Core/Url.php';
//require_once 'Customweb/Util/Url.php';
//require_once 'Customweb/Payment/Authorization/PaymentPage/IAdapter.php';



/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Beanstream_Authorization_PaymentPage_Adapter extends Customweb_Beanstream_Authorization_AbstractAdapter implements 
		Customweb_Payment_Authorization_PaymentPage_IAdapter {

	public function getAuthorizationMethodName(){
		return self::AUTHORIZATION_METHOD_NAME;
	}

	public function getAdapterPriority(){
		return 100;
	}

	public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		$paymentMethod = $this->getContainer()->getBean('Customweb_Beanstream_Method_Factory')->getPaymentMethod(
				$orderContext->getPaymentMethod(), self::AUTHORIZATION_METHOD_NAME);
		$paymentMethod->preValidate($orderContext, $paymentContext);
	}

	public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext){
		return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
	}

	/**
	 * This method returns true, when the given payment method supports recurring payments.
	 *
	 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
	 * @return boolean
	 */
	public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod){
		return false;
	}

	public function createTransaction(Customweb_Payment_Authorization_PaymentPage_ITransactionContext $transactionContext, $failedTransaction){
		$transaction = new Customweb_Beanstream_Authorization_Transaction($transactionContext);
		$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
		$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
		return $transaction;
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see Customweb_Payment_Authorization_PaymentPage_IAdapter::getVisibleFormFields()
	 */
	public function getVisibleFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $customerPaymentContext){
		if ($aliasTransaction !== null && $aliasTransaction !== 'new') {
			$factory = $this->getContainer()->getMethodFactory();
			return $factory->getPaymentMethod($orderContext->getPaymentMethod(), $this->getAuthorizationMethodName())->getFormFields(
					$orderContext, $aliasTransaction, $failedTransaction, self::AUTHORIZATION_METHOD_NAME, false, $customerPaymentContext);
		}
		else {
			return array();
		}
	}

	public function isHeaderRedirectionSupported(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$url = $this->getRedirectionUrl($transaction, $formData);
		if (strlen($url) > 2000) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see Customweb_Payment_Authorization_PaymentPage_IAdapter::getFormActionUrl()
	 */
	public function getFormActionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		if ($transaction->getTransactionContext()->getAlias() == "new" || $transaction->getTransactionContext()->getAlias() != null) {
			/*
			 * The alias manager with paymentpage must not be used, because an unsolvable problem with the parameter trnReturnUrl in
			 * http://support.beanstream.com/properties/external_pdfs/bean_payment_profiles.pdf exist. The way the return Url is constructed by
			 * Beanstream does not allow a proper handling with the Endpoint Adapter.
			 */
			return $this->getContainer()->getPaymentPageAliasFailedUrl($transaction->getExternalTransactionId());
		}
		else {
			return $this->getConfiguration()->getPaymentPageUrl();
		}
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see Customweb_Payment_Authorization_PaymentPage_IAdapter::getParameters()
	 */
	public function getParameters(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		$url = new Customweb_Core_Url($this->getRedirectionUrl($transaction, $formData));
		return $url->getQueryAsArray();
	}

	/**
	 * (non-PHPdoc)
	 * 
	 * @see Customweb_Payment_Authorization_PaymentPage_IAdapter::getRedirectionUrl()
	 */
	public function getRedirectionUrl(Customweb_Payment_Authorization_ITransaction $transaction, array $formData){
		try{
			$parameterBuilder = new Customweb_Beanstream_Authorization_PaymentPage_ParameterBuilder($transaction, $this->getContainer());
			$returnUrl = Customweb_Util_Url::appendParameters($this->getConfiguration()->getPaymentPageUrl(), 
					$parameterBuilder->buildStandardParameters());
			if ($transaction->getTransactionContext()->getAlias() == "new" || $transaction->getTransactionContext()->getAlias() != null) {
				/*
				 * The alias manager with paymentpage must not be used, because an unsolvable problem with the parameter trnReturnUrl in
				 * http://support.beanstream.com/properties/external_pdfs/bean_payment_profiles.pdf exist. The way the return Url is constructed by
				 * Beanstream does not allow a proper handling with the Endpoint Adapter.
				 */
				return $this->getContainer()->getPaymentPageAliasFailedUrl($transaction->getExternalTransactionId());
			}
			else {
				return $returnUrl;
			}
		}
		catch(Exception $e){
			if(!$transaction->isAuthorizationFailed()){
				$transaction->setAuthorizationFailed($e->getMessage());
			}
			return $transaction->getFailedUrl();
		}
	}

	public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
		return true;
	}


}