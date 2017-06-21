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

namespace Customweb\BeanstreamCw\Block\Payment\Method;

class Info extends \Magento\Payment\Block\Info
{
	/**
	 * @var \Customweb\BeanstreamCw\Model\Authorization\TransactionFactory
	 */
	protected $_transactionFactory;

	/**
	 * Payment method form template
	 *
	 * @var string
	 */
	protected $_template = 'Customweb_BeanstreamCw::payment/method/info.phtml';

	/**
	 * @var \Customweb\BeanstreamCw\Model\Authorization\Transaction
	 */
	private $transaction = null;

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Customweb\BeanstreamCw\Model\Authorization\TransactionFactory $transactionFactory
	 * @param array $data
	 */
	public function __construct(
			\Magento\Framework\View\Element\Template\Context $context,
			\Customweb\BeanstreamCw\Model\Authorization\TransactionFactory $transactionFactory,
			array $data = []
	) {
		parent::__construct($context, $data);
		$this->_transactionFactory = $transactionFactory;
	}

	public function getMethod()
	{
		$method = parent::getMethod();
		$method->setStore($this->getTransaction()->getStoreId());
		return $method;
	}

	/**
	 * @return \Customweb\BeanstreamCw\Model\Authorization\Transaction
	 */
	public function getTransaction()
	{
		if (!($this->transaction instanceof \Customweb\BeanstreamCw\Model\Authorization\Transaction)) {
			if ($this->getInfo() instanceof \Magento\Sales\Model\Order\Payment) {
				$transaction = $this->_transactionFactory->create()->loadByOrderId($this->getInfo()->getOrder()->getId());
				if ($transaction->getId()) {
					$this->transaction = $transaction;
				}
			}
		}
		return $this->transaction;
	}

	/**
	 * Get transaction view url.
	 *
	 * @return string
	 */
	public function getTransactionViewUrl()
	{
		return $this->getUrl('beanstreamcw/transaction/view', ['transaction_id' => $this->getTransaction()->getId()]);
	}

	/**
	 * @return boolean
	 */
	public function isShowMethodImage()
	{
		return $this->getMethod()->isShowImage();
	}
}
