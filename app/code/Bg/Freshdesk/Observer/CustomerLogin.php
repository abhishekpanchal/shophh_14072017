<?php
namespace Bg\Freshdesk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Bg\Freshdesk\Helper\Data as Helper;

class CustomerLogin implements ObserverInterface {

	/** @var \Magento\Framework\Logger\Monolog */
	protected $logger;
	protected $customerSession;
	protected $_responseFactory;
    protected $_url;
	
	public function __construct(
		\Psr\Log\LoggerInterface $loggerInterface,
		Helper $helper,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
	) {
		$this->logger = $loggerInterface;
		$this->_helper = $helper;
		$this->customerSession = $customerSession;
		$this->_responseFactory = $responseFactory;
        $this->_url = $url;
		
		
	}

	/**
	 * This is the method that fires when the event runs. 
	 * 
	 * @param Observer $observer
	 */
	public function execute( Observer $observer ) {
		
		
		$customer = $observer->getCustomer();
				
			if($this->_helper->getfdsso()){				
							
				
				
				if($this->customerSession->getData("freshdeskredirect", true)===1):
				
							
				$url=$this->_helper->getfdcustomerhome();
				$url2=$this->_helper->getSSOUrl($customer->getName(),$customer->getEmail(),$url);
				$this->_responseFactory->create()->setRedirect($url2)->sendResponse();
		       	//exit();      	
		       	endif;
			
			}
	}
}