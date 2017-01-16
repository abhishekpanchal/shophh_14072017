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

//require_once 'Customweb/Payment/Authorization/Method/CreditCard/CardHandler.php';
//require_once 'Customweb/Payment/Authorization/Method/CreditCard/CardInformation.php';
//require_once 'Customweb/Beanstream/Method/DefaultMethod.php';
//require_once 'Customweb/Payment/Authorization/Method/CreditCard/ElementBuilder.php';


/**
 *
 * @author Thomas Brenner
 * @Method(paymentMethods={'CreditCard', 'Visa', 'Mastercard', 'Maestro', 'CarteBancaire', 'AmericanExpress'})
 *
 */
class Customweb_Beanstream_Method_CreditCardMethod extends Customweb_Beanstream_Method_DefaultMethod {
	/**
	 * @param Customweb_Payment_Authorization_IOrderContext $orderContext
	 * @param Customweb_Beanstream_Authorization_Transaction $aliasTransaction
	 * @param Customweb_Beanstream_Authorization_Transaction $failedTransaction
	 * @param string $authorizationMethod
	 * @param Customweb_Payment_Authorization_IPaymentCustomerContext $customerPaymentContext
	 * @return array
	 */
	public function getFormFields(Customweb_Payment_Authorization_IOrderContext $orderContext, $aliasTransaction, $failedTransaction, $authorizationMethod, $customerPaymentContext) {
		$formBuilder = new Customweb_Payment_Authorization_Method_CreditCard_ElementBuilder($this->getCardHandler());

		//Pay with without alias token
		$formBuilder
		->setCardHolderName($orderContext->getBillingAddress()->getFirstName() . ' ' . $orderContext->getBillingAddress()->getLastName())
		->setCardHolderFieldName('card_holder')
		->setCardNumberFieldName('card_number')
		->setBrandFieldName('card_type')
		->setImageBrandSelectionActive()
		->setAutoBrandSelectionActive();
		
		
		
		if ($this->getPaymentMethodName() != 'creditcard') {
			$formBuilder->setSelectedBrand($this->getPaymentMethodName())->setFixedBrand(true);
		}

		//Alias transaction
		if($aliasTransaction != null && $aliasTransaction != 'new'){
			$formBuilder->setFixedBrand()
			->setFixedCardHolderActive()
			->setFixedCardExpiryActive()
			->setSelectedExpiryMonth($aliasTransaction->getExpiryMonth())
			->setSelectedExpiryYear($aliasTransaction->getExpiryYear())
			->setMaskedCreditCardNumber($aliasTransaction->getAliasForDisplay())
			->setForceCvcOptional(true)
			->setFixedBrand();
		}else{
			$formBuilder->setCvcFieldName('card_cvn');
		}
		return $formBuilder->build();
	}

	public function getCardHandler() {
		if ($this->getPaymentMethodName() == 'creditcard') {
			$cardInformations = Customweb_Payment_Authorization_Method_CreditCard_CardInformation::getCardInformationObjects(
					$this->getPaymentInformationMap(),
					$this->getPaymentMethodConfigurationValue('credit_card_brands'),
					'PaymentMethod'
			);
		}
		else {
			$cardInformations = Customweb_Payment_Authorization_Method_CreditCard_CardInformation::getCardInformationObjects(
					$this->getPaymentInformationMap(),
					$this->getPaymentMethodName(),
					'PaymentMethod'
			);
		}
		
		return new Customweb_Payment_Authorization_Method_CreditCard_CardHandler($cardInformations);
	}
	
	public function getPaymentMethodParameterName(){
		return 'card';
	}
}