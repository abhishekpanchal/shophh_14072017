<?php
namespace Bg\Freshdesk\Controller\Customer;

class Reply extends \Magento\Framework\App\Action\Action {

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


	$cutomerEmail='';

		 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$customerSession = $objectManager->get('Magento\Customer\Model\Session');
	if($customerSession->isLoggedIn()) {
	   // customer login action
	 
		 $cutomerEmail =(string)$customerSession->getCustomer()->getEmail();
	 
		 
	       
		}
		else{
		       $this->_redirect('customer/account/login/');
		    }
	     

	      
	 $data1['body'] = $this->getRequest()->getParam('ticket_reply_message');
	 $data1['user_id'] = (int)$this->getRequest()->getParam('userid');

	$id = (int)$this->getRequest()->getParam('ticketid');
	 

	$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();

	$api_key = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();
	$password = "x";

	$ticket_data = json_encode($data1);

	$url = $domain."api/v2/tickets";
	$url.="/".$id;
	$url.="/reply";

	$ch = curl_init($url);

	$header[] = "Content-type: application/json";
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$password");
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ticket_data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);

	curl_close($ch);

	if($info['http_code'] == 201) {
	 

	} else {
	  if($info['http_code'] == 404) {

	$this->messageManager->addError("Error, Please check the end point");
		      

	   
	  } else {

	$this->messageManager->addError($info['http_code']);

	    
	  }
	}


	$this->_redirect('freshdesk/customer/viewticket/id/'.$id);
    

    }

 
}
