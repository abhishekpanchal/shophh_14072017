<?php
namespace Bg\Freshdesk\Controller\Customer;

class Viewticket extends \Magento\Framework\App\Action\Action {

protected $resultPageFactory;

public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
 

public function execute()
    {

         $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
   // customer login action
 
$cutomerEmail =(string)$customerSession->getCustomer()->getEmail();
$id = $this->getRequest()->getParam('id');
$cutomerName =(string)$customerSession->getCustomer()->getName();
   
        }
        else{
               $this->_redirect('customer/account/login/');
            }
      

  	$this->_view->loadLayout();

    	$this->_view->renderLayout();

    }

}
