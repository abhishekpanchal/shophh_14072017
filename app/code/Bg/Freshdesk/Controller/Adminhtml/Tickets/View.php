<?php


namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;

class View extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{
    
    public function execute()
    {
       
$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();
$adminEmail=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdemail();

$id=$this->getRequest()->getParam('id');

$dashboard=$domain."helpdesk/tickets/".$id;

$resultRedirect = $this->resultRedirectFactory->create();
$resultRedirect->setUrl($dashboard);
return $resultRedirect;

    }
}
