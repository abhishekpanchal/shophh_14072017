<?php

namespace Bg\Freshdesk\Block\Adminhtml;

use \Magento\Framework\Stdlib\DateTime;
use \Bg\Freshdesk\Helper\Data as HelperData;
use \Magento\Framework\ObjectManagerInterface;

class Tickets extends \Magento\Backend\Block\Widget\Grid\Container
{

protected $_scopeConfig;
protected $_objectFactory;

protected $_coreRegistry;
protected $_nextlink;

   public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
   		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_nextlink=0;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context, $data);
    }

   protected function _construct()
    {
        $this->_controller = 'adminhtml_tickets';
        $this->_blockGroup = 'Bg_Freshdesk';
        $this->_headerText = __('Tickets');
        $this->_addButtonLabel = __('Create Ticket');
        parent::_construct();
	
    }
    
    public function getTimeAccordingToTimeZone($dateTime)
    {
    	// for get current time according to time zone
    	$today = $this->_timezoneInterface->date()->format('m/d/y H:i:s');
    
    	// for convert date time according to magento time zone
    	$dateTimeAsTimeZone = $this->_timezoneInterface
    	->date(new \DateTime($dateTime))
    	->format('m/d/y H:i:s');
    	return $dateTimeAsTimeZone;
    }

    function getfdstatus(){
    
    	$allfields=$this->getAvailableFields();
    	$status=array();
    	$priority=array();
    
    	$output=array();
    	    
    	foreach($allfields as $k=>$v){
    		if($v['name']=='status'){
    			foreach($v['choices'] as $k2=>$v2){
    				$status[$k2]=$v2[0];
    			}
    		}
    		
    		if($v['name']=='priority'){
    			foreach($v['choices'] as $k2=>$v2){
    				$priority[$v2]=$k2;
    			}
    		}
    		
    		
    	}
    $output=array('status'=>$status,'priority'=>$priority);
    return $output;
    
    }

public function getTickets()
{

	$password = "x";

	$domain=$this->getdomain();
	$api_key=$this->getapi();


	$orderid=$this->getfdorderid();
	$orderst=$this->checkfdorderid();
	
	$page = $this->getpage();

	$url = $domain."api/v2/tickets?";
	$url.="per_page=30";
	//$url.="&filter=new_and_my_open";
	//$datefrm=date('Y-m-d',strtotime('-6 Month'));
	$datefrm='2010-01-01';
	$url.="&updated_since=".$datefrm;
	if(empty($page)){
		$page=1;
	}
	$url.="&page=".$page;


	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$password");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$headers = substr($server_output, 0, $header_size);
	$response = substr($server_output, $header_size);
//print_r($headers);

	if(preg_match("/Link:/i", $headers, $match)) :
	$this->_nextlink=1;
	endif;


	if($info['http_code'] == 200) {
	  $result= $response;
	} else {
	  if($info['http_code'] == 404) {
	    
	  } else {
	    
	  }
	}

	curl_close($ch);

	$result=json_decode($result,true);

	$res=array();

	foreach($result as $r){

	$res[$r['id']]= [
	'subject' => $r['subject'],
	'status' => $r['status'],
	'created_at' => $this->getTimeAccordingToTimeZone($r['created_at']),
	'priority' => $r['priority'],
	'due_by' => $this->getTimeAccordingToTimeZone($r['due_by']),
	'responder_id' => $r['responder_id'],
	'requester_id' => $r['requester_id'],
	'order_id' => ($orderst)?$r['custom_fields'][$orderid]:'',
	'orderurl'=>($orderst)?$this->getadminorderurlid($r['custom_fields'][$orderid]):'',
	'url'=>$this->getUrl('freshdeskadmin/tickets/view/',array('id'=>$r['id']))
	];


	}

	$output= array('nextlink' => $this->_nextlink , 'tickets'=>$res);
	return $output;

}

function getadminorderurlid($id){
	if(empty($id)) return '';
	return $this->getUrl('sales/order/view/',array('order_id'=>$id));
}

function prevlink(){
	$page = $this->getpage();
	
	if($page>1){
		$page--;
	}else{
		return '';
	}
	return $this->getUrl('freshdeskadmin/tickets/index/',array('page'=>$page));
}

function nextlink(){
	
		$page = $this->getpage();
		if($page>1){
			$page++;
		}else{
			$page=2;
		}
		return $this->getUrl('freshdeskadmin/tickets/index/',array('page'=>$page));
	
}


function getAvailableAgent()
    {


	$password = "x";

	$domain=$this->getdomain();
	$api_key=$this->getapi();

	$url = $domain."api/v2/agents";


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

	$result=json_decode($result);

	$res['']='-';

	foreach($result as $r){

	$res[$r->id] = $r->contact->name;


	}

	return $res;
        
    }


function getAvailableContacts()
    {

	$domain=$this->getdomain();
	$api_key=$this->getapi();



	   
	$password = "x";

	$url = $domain."api/v2/contacts";



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

	$result=json_decode($result);

	$res['']='-';

	foreach($result as $r){

	$res[$r->id] = $r->name;


	}

	return $res;
        
    }

function getdomain(){

 return $this->_coreRegistry->registry('fddomain');

}


function getapi(){

 return $this->_coreRegistry->registry('fdapikey');

}


function getfdorderid(){
	//return '';
	//return $this->_coreRegistry->registry('fdorderid');
	return $this->getfdorder();
}

function getfdsso(){
	return $this->_coreRegistry->registry('fdsso');
	
}

function getfdssokey(){
	return $this->_coreRegistry->registry('fdssokey');
}

function getpage(){
	return (int) $this->_coreRegistry->registry('fdpage');
}

function getAvailableFields()
{

	$result=$this->curlget('ticketfields');
	return $result['output'];

}

function getfdorder(){

	$allfields=$this->getAvailableFields();
	$fieldset=array();
	$oid=$this->_coreRegistry->registry('fdorderid');
	$name='';
	if(!empty($oid)){

		foreach($allfields as $k=>$v){
			
			if($v['label_for_customers']==$oid){
				$name = $v['name'];
				return $name;
			}
		}

	}
	return $name;
}

function checkfdorderid(){
	$orderid=$this->getfdorderid();
	if(empty($orderid)){
		return 0;
	}else{
		return 1;
	}

}

function curlget($action,$data=array())
{

	$domain=$this->getdomain();
	$api_key=$this->getapi();

	switch($action)
	{
		
		case 'ticketfields':
			$url = $domain."api/v2/ticket_fields";
			break;



	}
	$result=array();
	$password = "x";
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
		$result=json_decode($result,true);
	} else {
		if($info['http_code'] == 404) {

		} else {

		}
	}

	$emsg='';
	switch($info['http_code']){

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

	
	curl_close($ch);


	$result= array('url'=>$url,'output'=>$result,'info'=>$info);
	return $result;

}





}
