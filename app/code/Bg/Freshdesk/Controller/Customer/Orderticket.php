<?php
namespace Bg\Freshdesk\Controller\Customer;

class Orderticket extends \Magento\Framework\App\Action\Action {

 public function execute() {

 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
   
         
       
        }
        else{
              $this->_redirect('customer/account/login/');
            }

    $this->_view->loadLayout();

    $this->_view->renderLayout();
  }

}
