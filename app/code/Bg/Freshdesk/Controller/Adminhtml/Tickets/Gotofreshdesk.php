<?php


namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;

class Gotofreshdesk extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{
    
    public function execute()
    {
       
$dashboard=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddashboard();

$resultRedirect = $this->resultRedirectFactory->create();
$resultRedirect->setUrl($dashboard);
return $resultRedirect;

    }
}
