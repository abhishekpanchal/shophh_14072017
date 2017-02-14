<?php

namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;

class Index extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{
    
    public function execute()
    {

		$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();
		$api=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();
	    $orderid=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdorderid();
		$sso=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdsso();
		$ssokey=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdssokey();
	
		$this->_coreRegistry->register('fdorderid', $orderid);
		$this->_coreRegistry->register('fdsso', $sso);
		$this->_coreRegistry->register('fdssokey', $ssokey);
		$this->_coreRegistry->register('fddomain', $domain);
		$this->_coreRegistry->register('fdapikey', $api);
		
		$page = $this->getRequest()->getParam('page');
		$this->_coreRegistry->register('fdpage', $page);
	
	    $resultPage = $this->_resultPageFactory->create();
	
	    return $resultPage;
    }
}
