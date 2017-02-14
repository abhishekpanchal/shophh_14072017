<?php

namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\HTTP\Adapter\Curl;
use Psr\Log\LoggerInterface;

class Save extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{

 protected $_helper= null;
 protected $_curl = '';


    public function execute()
    {
       

$savefields[]=array('name'=>'name','type'=>'string');
$savefields[]=array('name'=>'requester_id','type'=>'number');
$savefields[]=array('name'=>'email','type'=>'string');
$savefields[]=array('name'=>'facebook_id','type'=>'string');
$savefields[]=array('name'=>'phone','type'=>'number');
$savefields[]=array('name'=>'twitter_id','type'=>'string');
$savefields[]=array('name'=>'subject','type'=>'string');
$savefields[]=array('name'=>'type','type'=>'string');
$savefields[]=array('name'=>'status','type'=>'number');
$savefields[]=array('name'=>'priority','type'=>'number');
$savefields[]=array('name'=>'description','type'=>'string');
$savefields[]=array('name'=>'responder_id','type'=>'number');
$savefields[]=array('name'=>'attachments','type'=>'array of objects');
$savefields[]=array('name'=>'cc_emails','type'=>'array of strings');
$savefields[]=array('name'=>'due_by','type'=>'datetime');
$savefields[]=array('name'=>'email_config_id','type'=>'number');
$savefields[]=array('name'=>'fr_due_by','type'=>'datetime');
$savefields[]=array('name'=>'group_id','type'=>'number');
$savefields[]=array('name'=>'product_id','type'=>'number');
$savefields[]=array('name'=>'source','type'=>'number');
$savefields[]=array('name'=>'tags','type'=>'array of strings');



$indata=$this->getRequest()->getPostValue();

$allfields = $this->getAvailableFields();

foreach($allfields as $k=>$v){
$fieldtype[$v['name']]=$v['type'];
}

$data1['email'] = $this->getRequest()->getParam('requester');
$data1['subject'] = $this->getRequest()->getParam('subject');
$data1['description'] = $this->getRequest()->getParam('description');
$data1['status'] = (int)$this->getRequest()->getParam('status');
$data1['priority'] = (int)$this->getRequest()->getParam('priority');

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

switch($fieldtype[$k2]){

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


$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();

$api_key = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();
$password = "x";
$ticket_data = json_encode($data1);

$url = $domain."api/v2/tickets";
$header[] = "Content-type: application/json";


$ch = curl_init($url);
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
 $this->_getSession()->setFormData(false);

} else {
  if($info['http_code'] == 404) {

$this->messageManager->addError("Error, Please check the end point");
               

   
  } else {

$this->messageManager->addError($this->errorMsg($info['http_code']));

    
  }
}


$this->_redirect('freshdeskadmin/tickets/new');
  
            
           
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
	    
	  }
	}

	curl_close($ch);

	$result=json_decode($result,true);

	return $result;
        
    }

}
