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

//require_once 'Customweb/Payment/Authorization/Recurring/IAdapter.php';
//require_once 'Customweb/Beanstream/Authorization/AbstractAdapter.php';
//require_once 'Customweb/Beanstream/Authorization/Transaction.php';
//require_once 'Customweb/Beanstream/Authorization/Ajax/ParameterBuilder.php';


/**
 *
 * @author Thomas Brenner
 * @Bean
 *
 */
class Customweb_Beanstream_Authorization_Recurring_Adapter extends Customweb_Beanstream_Authorization_AbstractAdapter 
	implements Customweb_Payment_Authorization_Recurring_IAdapter {
		
		public function isAuthorizationMethodSupported(Customweb_Payment_Authorization_IOrderContext $orderContext){
			return true;
		}
		
		public function getAuthorizationMethodName(){
			return self::AUTHORIZATION_METHOD_NAME;
		}
		
		public function getAdapterPriority() {
			return 300;
		}
		
		public function isDeferredCapturingSupported(Customweb_Payment_Authorization_IOrderContext $orderContext, Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext) {
			return $orderContext->getPaymentMethod()->existsPaymentMethodConfigurationValue('capturing');
		}
		
		public function preValidate(Customweb_Payment_Authorization_IOrderContext $orderContext,
				Customweb_Payment_Authorization_IPaymentCustomerContext $paymentContext) {
			$paymentMethod = $this->getContainer()->getBean('Customweb_Beanstream_Method_Factory')->getPaymentMethod($orderContext->getPaymentMethod(), self::AUTHORIZATION_METHOD_NAME);
			$paymentMethod->preValidate($orderContext, $paymentContext);
		}
		
		/**
		 * This method returns true, when the given payment method supports recurring payments.
		 *
		 * @param Customweb_Payment_Authorization_IPaymentMethod $paymentMethod
		 * @return boolean
		 */
		public function isPaymentMethodSupportingRecurring(Customweb_Payment_Authorization_IPaymentMethod $paymentMethod) {
			return true;
		}
		
		/**
		 * This method creates a new recurring transaction.
		 *
		 * @param Customweb_Payment_Recurring_ITransactionContext $transactionContext
		*/
		public function createTransaction(Customweb_Payment_Authorization_Recurring_ITransactionContext $transactionContext){
			$transaction = new Customweb_Beanstream_Authorization_Transaction($transactionContext);
			$transaction->setAuthorizationMethod(self::AUTHORIZATION_METHOD_NAME);
			$transaction->setLiveTransaction(!$this->getConfiguration()->isTestMode());
			return $transaction;
		}
		
		/**
		 * This method debits the given recurring transaction on the customers card.
		 *
		 * @param Customweb_Payment_Authorization_ITransaction $transaction
		 * @throws If something goes wrong
		 * @return void
		*/
		public function process(Customweb_Payment_Authorization_ITransaction $transaction) {
			$oldTransaction = $transaction->getTransactionContext()->getInitialTransaction();
			$parameterBuilder = new Customweb_Beanstream_Authorization_Ajax_ParameterBuilder($transaction, $this->getContainer());
			try {
				$this->sendAliasRequest($transaction, $parameterBuilder->buildRecurringChargeParameters($oldTransaction->getCustomerCode()));
			}
			catch(Exception $e) {
				$transaction->setAuthorizationFailed($e->getMessage());
			}
		}

	

}