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

//require_once 'Customweb/Beanstream/Container.php';
//require_once 'Customweb/I18n/Translation.php';
//require_once 'Customweb/Core/Http/Client/Factory.php';
//require_once 'Customweb/Core/Http/Request.php';
//require_once 'Customweb/Payment/Util.php';


class Customweb_Beanstream_AbstractAdapter
{
	/**
	 * Configuration object.
	 *
	 * @var Customweb_Beanstream_Configuration
	 */
	private $configuration;
	
	/**
	 *
	 * @var Customweb_Beanstream_Container
	 */
	private $container;

	
	public function __construct(Customweb_DependencyInjection_IContainer $container) {
			$this->container = new Customweb_Beanstream_Container($container);
			$this->configuration = $this->getContainer()->getBean('Customweb_Beanstream_Configuration');
	}
	
	/**
	 * @return Customweb_Beanstream_Configuration
	 */
	public function getConfiguration() {
		return $this->configuration;
	}
	
	public function setConfiguration(Customweb_Beanstream_Configuration $configuration) {
		$this->configuration = $configuration;
	}
	
	
	public function getContainer() {
		return $this->container;
	}
	
	
	/**
	 *
	 * Performs an HTTP Query
	 *
	 * @param String $url
	 * @param array $body
	 * 
	 * @return Body
	 */
	public function sendRequest($url, $body, $merchantId, $passcode, $method) {
		$request = new Customweb_Core_Http_Request();
		$request->setBody($body);
		$request->setUrl($url);
		$request->setMethod($method);
		$request->setContentType('application/json');
		$request->appendHeader("Authorization: Passcode " . base64_encode($merchantId . ":" . $passcode));
		$client = Customweb_Core_Http_Client_Factory::createClient();
		$client->disableCertificateAuthorityCheck();
		
		$response = $client->send($request);
		$responseDecoded = json_decode($response->getBody(), true);
		if(isset($responseDecoded['error'])) {
			throw new Exception(Customweb_I18n_Translation::__($responseDecoded['error']['message']));
		}
		
		return $responseDecoded;
	}
	
	protected final function getTransactionAppliedSchema(Customweb_Payment_Authorization_ITransaction $transaction)
	{
		$schema = $this->getConfiguration()->getOrderIdSchema();
		$id = $transaction->getTransactionContext()->getTransactionId();
	
		return preg_replace("/[^A-Za-z0-9]/", '', Customweb_Payment_Util::applyOrderSchema($schema, $id, 64));
	}
}