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

//require_once 'Customweb/Payment/Endpoint/Controller/Abstract.php';
//require_once 'Customweb/Payment/Endpoint/Annotation/ExtractionMethod.php';



/**
 *
 * @author Nico Eigenmann
 *
 */
class Customweb_Beanstream_Endpoint_Abstract extends Customweb_Payment_Endpoint_Controller_Abstract	 {
	
	
	/**
	 * @param Customweb_Core_Http_IRequest $request
	 * @ExtractionMethod
	 */
	public function getTransactionId(Customweb_Core_Http_IRequest $request) {
		$parameters = $request->getParameters();
		if (isset($parameters['cw_transaction_id'])) {
			return array(
				'id' => $parameters['cw_transaction_id'],
				'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::EXTERNAL_TRANSACTION_ID_KEY,
			);
		}
	
		if (isset($parameters['ref1'])) {
			return array(
				'id' => $parameters['ref1'],
				'key' => Customweb_Payment_Endpoint_Annotation_ExtractionMethod::EXTERNAL_TRANSACTION_ID_KEY,
			);
		}

		throw new Exception("No transaction id present in the request.");
	}
}