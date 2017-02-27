<?php
namespace Bg\Freshdesk\Controller\Sso;

class Logout extends \Magento\Framework\App\Action\Action {
	
 public function execute() {

 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
   
	         $this->_redirect('customer/account/logout/');
        }
        else{
        	
        	$this->_redirect('/');
        	   
            }


$this->_view->loadLayout();
$this->_view->renderLayout();
  }
  

}
