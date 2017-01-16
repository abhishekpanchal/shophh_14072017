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
 *
 * @category	Customweb
 * @package		Customweb_BeanstreamCw
 * 
 */

namespace Customweb\BeanstreamCw\Model;

class DependencyContainer implements \Customweb_DependencyInjection_IContainer {

	/**
	 * @var \Customweb_DependencyInjection_IContainer
	 */
	private $container = null;

	/**
	 * @var \Customweb\BeanstreamCw\Model\EndpointAdapter
	 */
	private $_endpointAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\ConfigurationAdapter
	 */
	private $_configurationAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\StorageBackend
	 */
	private $_storageBackendAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\Adapter\LayoutRenderer
	 */
	private $_layoutRenderer;

	/**
	 * @var \Customweb\BeanstreamCw\Model\Authorization\TransactionHandler
	 */
	private $_transactionHandler;

	/**
	 * @var \Customweb\BeanstreamCw\Model\Update\Handler
	 */
	private $_updateHandler;

	/**
	 * @var \Customweb\BeanstreamCw\Model\Asset\CompositeResolver
	 */
	private $_assetResolver;

	/**
	 * @var \Customweb\BeanstreamCw\Model\BackendOperation\CancelAdapter
	 */
	private $_cancelAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\BackendOperation\CaptureAdapter
	 */
	private $_captureAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\BackendOperation\RefundAdapter
	 */
	private $_refundAdapter;

	/**
	 * @var \Customweb\BeanstreamCw\Model\ExternalCheckout\Service
	 */
	private $_externalCheckoutService;

	/**
	 * @param \Customweb\BeanstreamCw\Model\Adapter\Endpoint $endpointAdapter
	 * @param \Customweb\BeanstreamCw\Model\Adapter\Configuration $configurationAdapter
	 * @param \Customweb\BeanstreamCw\Model\Adapter\StorageBackend $storageBackendAdapter
	 * @param \Customweb\BeanstreamCw\Model\Adapter\LayoutRenderer $layoutRenderer
	 * @param \Customweb\BeanstreamCw\Model\Authorization\TransactionHandler $transactionHandler
	 * @param \Customweb\BeanstreamCw\Model\Update\Handler $updateHandler
	 * @param \Customweb\BeanstreamCw\Model\Asset\CompositeResolver $assetResolver
	 * @param \Customweb\BeanstreamCw\Model\BackendOperation\CancelAdapter $cancelAdapter
	 * @param \Customweb\BeanstreamCw\Model\BackendOperation\CaptureAdapter $captureAdapter
	 * @param \Customweb\BeanstreamCw\Model\BackendOperation\RefundAdapter $refundAdapter,
	 * @param \Customweb\BeanstreamCw\Model\ExternalCheckout\Service $externalCheckoutService
	 */
	public function __construct(
			\Customweb\BeanstreamCw\Model\Adapter\Endpoint $endpointAdapter,
			\Customweb\BeanstreamCw\Model\Adapter\Configuration $configurationAdapter,
			\Customweb\BeanstreamCw\Model\Adapter\StorageBackend $storageBackendAdapter,
			\Customweb\BeanstreamCw\Model\Adapter\LayoutRenderer $layoutRenderer,
			\Customweb\BeanstreamCw\Model\Authorization\TransactionHandler $transactionHandler,
			\Customweb\BeanstreamCw\Model\Update\Handler $updateHandler,
			\Customweb\BeanstreamCw\Model\Asset\CompositeResolver $assetResolver,
			\Customweb\BeanstreamCw\Model\BackendOperation\CancelAdapter $cancelAdapter,
			\Customweb\BeanstreamCw\Model\BackendOperation\CaptureAdapter $captureAdapter,
			\Customweb\BeanstreamCw\Model\BackendOperation\RefundAdapter $refundAdapter,
			\Customweb\BeanstreamCw\Model\ExternalCheckout\Service $externalCheckoutService
	) {
		$this->_endpointAdapter = $endpointAdapter;
		$this->_configurationAdapter = $configurationAdapter;
		$this->_storageBackendAdapter = $storageBackendAdapter;
		$this->_layoutRenderer = $layoutRenderer;
		$this->_transactionHandler = $transactionHandler;
		$this->_updateHandler = $updateHandler->setContainer($this);
		$this->_assetResolver = $assetResolver;
		$this->_cancelAdapter = $cancelAdapter;
		$this->_captureAdapter = $captureAdapter;
		$this->_refundAdapter = $refundAdapter;
		$this->_externalCheckoutService = $externalCheckoutService->setContainer($this);
	}

	public function getBean($identifier) {
		return $this->instance()->getBean($identifier);
	}

	public function getBeansByType($type) {
		return $this->instance()->getBeansByType($type);
	}

	public function hasBean($identifier) {
		return $this->instance()->hasBean($identifier);
	}

	/**
	 * @return \Customweb_DependencyInjection_IContainer
	 */
	private function instance() {
		if ($this->container === null) {
			\Customweb_Core_Util_Class::registerClassLoader(function($className) {
				return \Magento\Framework\Autoload\AutoloaderRegistry::getAutoloader()->loadClass($className);
			});

			$packages = array(
			0 => 'Customweb_Beanstream',
 			1 => 'Customweb_Payment_Authorization',
 		);
			$packages[] = 'Customweb_BeanstreamCw_Model_';
			$packages[] = 'Customweb_Mvc_Template_Php_Renderer';
			$packages[] = 'Customweb_Payment_SettingHandler';

			$provider = new \Customweb_DependencyInjection_Bean_Provider_Editable(new \Customweb_DependencyInjection_Bean_Provider_Annotation(
					$packages
			));
			$provider->addObject(\Customweb_Core_Http_ContextRequest::getInstance());
			$provider->addObject($this->_endpointAdapter);
			$provider->addObject($this->_configurationAdapter);
			$provider->addObject($this->_storageBackendAdapter);
			$provider->addObject($this->_layoutRenderer);
			$provider->addObject($this->_transactionHandler);
			$provider->addObject($this->_updateHandler);
			$provider->addObject($this->_assetResolver);
			$provider->addObject($this->_cancelAdapter);
			$provider->addObject($this->_captureAdapter);
			$provider->addObject($this->_refundAdapter);
			$provider->addObject($this->_externalCheckoutService);
			$this->container = new \Customweb_DependencyInjection_Container_Default($provider);
		}
		return $this->container;
	}
}