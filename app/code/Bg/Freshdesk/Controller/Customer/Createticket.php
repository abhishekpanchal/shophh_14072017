<?php
namespace Bg\Freshdesk\Controller\Customer;

class Createticket extends \Magento\Framework\App\Action\Action {

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
   
 
         $cutomerEmail =(string)$customerSession->getCustomer()->getEmail();
 
         
       
        }
        else{
              $this->_redirect('customer/account/login/');
            }
      
      
 $data1['email'] = $cutomerEmail;
 $data1['subject'] = $this->getRequest()->getParam('subject');
 $data1['status'] = 2;
 $data1['priority'] = 1;
 $data1['description'] = $this->getRequest()->getParam('description');

/*$orderid=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdorderid();
if(!empty($orderid)){
$data1['custom_fields'][$orderid]=$this->getRequest()->getParam('order_id');
}*/

    $indata=$this->getRequest()->getPostValue();

$allfields = $this->getAvailableFields();

foreach($allfields as $k=>$v){
$fieldtype[$v['name']]=$v['type'];
}


if(!empty($this->getRequest()->getParam('ticket_type'))){
$data1['type'] = $this->getRequest()->getParam('ticket_type');
}

if(!empty($this->getRequest()->getParam('source'))){
$data1['source'] = (int)$this->getRequest()->getParam('source');
}


if(!empty($this->getRequest()->getParam('group'))){
$data1['group_id'] = (int)$this->getRequest()->getParam('group');
}

if(!empty($this->getRequest()->getParam('agent'))){
$data1['responder_id'] = (int)$this->getRequest()->getParam('agent');
}

if(!empty($this->getRequest()->getParam('product'))){
$data1['product_id'] = (int)$this->getRequest()->getParam('product');
}



unset($indata['requester'],$indata['subject'],$indata['ticket_type'],$indata['source'],$indata['status'],$indata['priority'],$indata['group'],$indata['agent'],$indata['description'],$indata['form_key'],$indata['product']);

foreach($indata as $k2=>$v2){
if(!empty($v2)){
$sw='';
	if(array_key_exists($k2, $fieldtype)){
		$sw=$fieldtype[$k2];
	}
switch($sw){

case 'custom_number':
$data1['custom_fields'][$k2]=(int)$v2;
break;

case 'custom_decimal':
$data1['custom_fields'][$k2]=(float)$v2;
break;

case 'custom_date':
$data1['custom_fields'][$k2]=date('Y-m-d',strtotime($v2));
break;

default:
$data1['custom_fields'][$k2]=$v2;
}

}
}



if(empty($data1['subject']) || empty($data1['description']) || empty($data1['email'])){
$this->messageManager->addError("Error, Please fill out fields");
$this->_redirect('freshdesk/customer/newticket/');
}

$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();

$api_key = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();
$password = "x";

$ticket_data = json_encode($data1);

$url = $domain."api/v2/tickets";

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
 
 $this->messageManager->addSuccess(__('Ticket created successfully.'));

} else {
  if($info['http_code'] == 404) {

$this->messageManager->addError("Error, Please check the end point");
             

   
  } else {

$this->messageManager->addError($this->errorMsg($info['http_code']));
    
  }
}



$this->_redirect('freshdesk/customer/index');
  
  

    }
    
function getAvailableFields()
    {


	$password = "x";

	$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();

	$api_key = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();


	$url = $domain."api/v2/ticket_fields";



	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$password");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$headers = substr($server_output, 0, $header_size);
	$response = substr($server_output, $header_size);

	if($info['http_code'] == 200) {
	  $result= $response;
	} else {
	  if($info['http_code'] == 404) {
	    
	  } else {
	    $this->errorMsg($info['http_code']);
	  }
	}

	curl_close($ch);

	$result=json_decode($result,true);

	return $result;
        
    }
    
function errorMsg($code){
    	
    $emsg='';
	switch($code){
		
		case 400:
			$emsg="Client or Validation Error";
			break;
		case 401:
			$emsg="Authentication Failure";
			break;
		case 403:
			$emsg="Access Denied";
			break;
		case 404:
			$emsg="Requested Resource not Found";
			break;
		case 405:
			$emsg="Method not allowed";
			break;
		case 406:
			$emsg="Unsupported Accept Header";
			break;
		case 409:
			$emsg="Inconsistent/Conflicting State";
			break;
		case 415:
			$emsg="Unsupported Content-type";
			break;
		case 429:
			$emsg="Rate Limit Exceeded";
			break;
		case 500:
			$emsg="Unexpected Server Error";
			break;
	}

	if(!empty($emsg)):
		$this->_objectManager->get('Psr\Log\LoggerInterface')->addDebug($emsg);
	endif;
	
	return $emsg;
    	
}

}
