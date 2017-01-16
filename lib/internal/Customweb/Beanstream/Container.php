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

//require_once 'Customweb/Payment/AbstractContainer.php';


class Customweb_Beanstream_Container extends Customweb_Payment_AbstractContainer
{
	
	public function getSuccessReturnTokenUrl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('aProcess', 'str', array('cw_transaction_id' => $transactionId));
	}
	
	public function getFailedReturnTokenUrl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('aProcess', 'ftr', array('cw_transaction_id' => $transactionId));
	}
	
	public function getNewAliasUrl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('aProcess', 'na', array('cw_transaction_id' => $transactionId));
	}
	
	public function getExistingAliasUrl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('aProcess', 'pa', array('cw_transaction_id' => $transactionId));
	}
	
 	public function getPaymentPageProcessUrl($transactionId){
 		return $this->getEndpointAdapter()->getUrl('ppProcess', 'process', array('cw_transaction_id' => $transactionId));
 	}
	
	public function getPaymentPageAliasFailedUrl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('ppProcess', 'afail', array('cw_transaction_id' => $transactionId));
	}
	
	public function getThreeDurl($transactionId) {
		return $this->getEndpointAdapter()->getUrl('aProcess', 'tds', array('cw_transaction_id' => $transactionId));
	}

	/**
	 *  @return Customweb_Beanstream_Configuration
	 */
	public function getConfiguration() {
		return $this->getBean('Customweb_Beanstream_Configuration');
	}

	/**
	 *  @return Customweb_Beanstream_Method_Factory
	 */
	public function getMethodFactory() {
		return $this->getBean('Customweb_Beanstream_Method_Factory');
	}
}