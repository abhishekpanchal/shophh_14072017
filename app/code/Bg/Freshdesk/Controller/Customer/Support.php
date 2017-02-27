<?php
namespace Bg\Freshdesk\Controller\Customer;

class Support extends \Magento\Framework\App\Action\Action {

 public function execute() {

 

$cutomerEmail='';
	$cutomerName='';
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$customerSession = $objectManager->get('Magento\Customer\Model\Session');

	if($customerSession->isLoggedIn()) {  

		$cutomerEmail =(string)$customerSession->getCustomer()->getEmail();
		$cutomerName =(string)$customerSession->getCustomer()->getName();
		$url2 = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();
		$url= $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getSSOUrl($cutomerName,$cutomerEmail,$url2);
	     
	}
	else
	{
		$url = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();
		
	}

$this->_redirect($url);


  }

}
