<?php
namespace Bg\Freshdesk\Block;

use \Magento\Framework\Stdlib\DateTime;
use Bg\Freshdesk\Helper\Data as HelperData;
use Magento\Framework\View\Element\Template\Context;

class Tickets extends \Magento\Framework\View\Element\Template
{

protected $_scopeConfig;
protected $_objectFactory;
protected $_request;
protected $_ticketid;
protected $_helper;
protected $response;
protected $_messageManager;
protected $logger;

public function __construct(
Context $context,
HelperData $helperdata,
\Magento\Framework\App\Response\Http $response,
\Magento\Framework\Message\ManagerInterface $messageManager,
\Psr\Log\LoggerInterface $logger,
array $data = []
    ) {

$this->_helper=$helperdata;
$this->response = $response;
$this->logger = $logger;
$this->_messageManager = $messageManager;

parent::__construct($context, $data);
$this->_isScopePrivate = true;
    }

public function _prepareLayout()
{
   //set page title
   $this->pageConfig->getTitle()->set(__('Tickets'));

   return parent::_prepareLayout();
} 

function datetimeconvert($date){
	return $this->_helper->getTimeAccordingToTimeZone($date);
}
 

public function getCustomerMail(){

$cutomerEmail='';

try{
	
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
  
$cutomerEmail =(string)$customerSession->getCustomer()->getEmail();

return $cutomerEmail;
       
        }
        else{
        	
$this->response->setRedirect($this->getUrl('customer/account/login/'));

            }
      }catch (\Exception $e) {
      	
            $e->getMessage();
            
}

return $cutomerEmail;

}

public function getCustomerName(){

$cutomerEmail='';

try{
        
$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
   
 
         $cutomerEmail =(string)$customerSession->getCustomer()->getName();
 
         
return $cutomerEmail;
       
        }
        else{

//$this->response->setRedirect($this->getUrl('customer/account/login/'));
            }
      }catch (\Exception $e) {
     
            $e->getMessage();
}

return $cutomerEmail;

}

function getCustomerId()
    {

	$data['email']=$this->getCustomerMail();
	$result=$this->curlget('contacts',$data);
	
	$res='';

	foreach($result['output'] as $r){

	$res = $r['id'];


	}

	return $res;
        
    }

public function getTickets()
{

$data['email']=$this->getCustomerMail();

$result = $this->curlget('mytickets',$data);


$res=array();
$orderid=$this->getfdorder();


foreach($result['output'] as $r){

$res[$r['id']]= [
'subject' => $r['subject'],
'status' => $r['status'],
'created_at' => $this->_helper->getTimeAccordingToTimeZone($r['created_at']),
'priority' => $r['priority'],
'due_by' => $this->_helper->getTimeAccordingToTimeZone($r['due_by']),
'responder_id' => $r['responder_id'],
'requester_id' => $r['requester_id'],
'order_id' => (!empty($orderid))?$r['custom_fields'][$orderid]:'',
'orderurl'=> (!empty($orderid))?$this->getorderurlid($r['custom_fields'][$orderid]):'',
'url'=> $this->getUrl('freshdesk/customer/viewticket/id/'.$r['id'])
];


}

return $res;


}

public function getRecentTickets()
{

$data['email']=$this->getCustomerMail();
$result = $this->curlget('recentticket',$data);

$res=array();
$orderid=$this->getfdorder();

foreach($result['output'] as $r){

$res[$r['id']]= [
'subject' => $r['subject'],
'status' => $r['status'],
'created_at' => $this->_helper->getTimeAccordingToTimeZone($r['created_at']),
'priority' => $r['priority'],
'due_by' => $this->_helper->getTimeAccordingToTimeZone($r['due_by']),
'responder_id' => $r['responder_id'],
'requester_id' => $r['requester_id'],
'order_id' => (!empty($orderid))?$r['custom_fields'][$orderid]:'',
'orderurl'=> (!empty($orderid))?$this->getorderurlid($r['custom_fields'][$orderid]):'',
'url'=> $this->getUrl('freshdesk/customer/viewticket/id/'.$r['id'])
];


}

return $res;

}


function getAvailableAgent()
{

	$result=$this->curlget('listagents');

	$res['']='-';

	foreach($result['output'] as $r){

	$res[$r['id']] = $r['contact']['name'];

	}

	return $res;
        
}

function getAvailableFields()
{

	$result=$this->curlget('ticketfields');
	return $result['output'];
        
}


function getdomain(){

$domain= $this->_scopeConfig->getValue(
            "freshdesk_section/account/text_domainname",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

return $domain;

}


function getTicketid(){
$this->_ticketid = $this->_request->getParam("id");
return $this->_ticketid;
}

function setTicketid($id){
$this->_ticketid = $id ;
}



function getTicketDetails(){
	
	//return array();
	$result='';
	$data['id']=$this->getTicketid();
	$result = $this->curlget("viewticket",$data);
$cid=$this->getCustomerId();

if($cid==$result['output']['requester_id']){
	return $result;

}
else{
$this->_messageManager->addError("Ticket Not Valid / Removed");
$this->response->setRedirect($this->getUrl('freshdesk/customer/index'));

}

return array();
}


function getcreateurl(){

return $this->getUrl('freshdesk/customer/newticket');

}

function getmyticketurl(){

return $this->getUrl('freshdesk/customer/index');

}

function getsaveticketurl(){

return $this->getUrl('freshdesk/customer/createticket');

}

function getreplyticketurl(){

return $this->getUrl('freshdesk/customer/reply');

}

function getOrderId(){
return $this->_request->getParam("order_id");
}

function getorderurl(){
$id=$this->getOrderId();
return $this->getUrl('sales/order/view/',array('order_id'=>$id));

}

function getorderurlid($id){
if(empty($id)) return '';
return $this->getUrl('sales/order/view/',array('order_id'=>$id));
}

function checkcustomerticket()
{
return $this->_helper->getfdcustomerticket();
}

function checkfdrecent()
{

if($this->checkcustomerticket()):

return $this->_helper->getfdrecent();

endif;

return 0;


}

function curlpost($url,$data)
{



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

function getfdorder(){

	$allfields=$this->getAvailableFields();
	$fieldset=array();
	$oid=$this->_helper->getfdorderid();
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

/*function getfdorder(){
return $this->_helper->getfdorderid();
}*/


function checkfdorderid(){
$orderid=$this->getfdorder();
if(empty($orderid)){
return 0;
}else{
return 1;
}

}

function curlget($action,$data=array())
{

$domain=$this->_helper->getfddomain();
$api_key=$this->_helper->getfdapi();

switch($action)
{
case 'viewticket':
$url = $domain."api/v2/tickets";
$url .="/".$data['id'];
$url.="?include=conversations";
break;

case 'listagents':
$url = $domain."api/v2/agents";
break;

case 'recentticket':
$url = $domain."api/v2/tickets";
$url .="?order_by=created_at&order_type=desc&per_page=5&page=1&email=".urlencode($data['email']);
break;

case 'mytickets':
$url = $domain."api/v2/tickets";
$url .="?email=".urlencode($data['email']);
break;

case 'contacts':
$url = $domain."api/v2/contacts?email=".urlencode($data['email']);
break;

case 'ticketfields':
	$url = $domain."api/v2/ticket_fields";
break;
	


}

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
$result=array();

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

if(!empty($emsg)):
	$emsg.=" Action ".$action;
	$this->logger->debug($emsg);
endif;

curl_close($ch);


$result= array('url'=>$url,'output'=>$result,'info'=>$info);
return $result;

}

function frmfield($type,$name,$option){
	switch($type){
		case 'text':
			return $this->frmtext($name,$option);
			break;
		case 'textarea':
			return $this->frmtextarea($name,$option);
			break;
		case 'select':
			return $this->frmselect($name,$option);
			break;
		case 'date':
			return $this->frmdate($name,$option);
			break;
		case 'nselect':
			$allchoices[]=array('label'=>'','value'=>'','children'=>array());
			
			foreach($option['options']['choices'] as $fk=>$fv){
				
				if(is_array($fv)){
					$level1choices=array();
					$level1choices[]=array('label'=>'--','value'=>'','children'=>array());
					foreach($fv as $fkL1=>$fvL1){
						
						if(is_array($fvL1)){
							$level2choices=array();
							$level2choices[]=array('label'=>'--','value'=>'','children'=>array());
							foreach($fvL1 as $fkL2=>$fvL2){
								$level2choices[]=array('label'=>$fvL2,'value'=>$fvL2,'children'=>array());
							}
							$level1choices[]=array('label'=>$fkL1,'value'=>$fkL1,'children'=>$level2choices);
						}
						else{
							$level1choices[]=array('label'=>$fkL1,'value'=>$fkL1,'children'=>array());
						}
						
					}
					$allchoices[]=array('label'=>$fk,'value'=>$fk,'children'=>$level1choices);
				}else{
						$allchoices[]=array('label'=>$fk,'value'=>$fk,'children'=>array());
					}
			}
			
			
			$newchoices[]='--';		
			foreach($option['options']['choices'] as $nfk=>$nfv){
				$newchoices[$nfk]=$nfk;
			}
			
			$option['choices']=$newchoices;
			
			$fi=$this->frmnselect($name,$option);
			$lx=0;
			foreach($option['options']['nested'] as $nk=>$nv){
				$lx++;
				$nvoption=array();
				$nvoption['label']=$nv['label'];
				$nvoption['choices']=array();
				$levelid=$name.'_level'.$lx."_block";
				$fi.=$this->frmnestedselect($nv['name'],$nvoption,$levelid,$name);
			}
			
			$fi.= '<script>';
				$fi.= 'fdNestedOptions["'.$name.'"] = new Freshdesk.Fields.Nested("'.$name.'",'. json_encode($allchoices) .');';
			$fi.= '</script>';
			return $fi;
			break;
		case 'checkbox':
			return $this->frmcheckbox($name,$option);
			break;
		default:
			return $this->frmtext($name,$option);
			break;
	}
	
	
}

function frmtext($name,$option){
	
	 $txt='<div class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<input type="text" class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;
	 
	 if(array_key_exists('value', $option)):
		 if(!empty($option['value'])){
		 	$txt.=' value="'.$option['value'].'"';
		 }
	 endif;
	 
	 $txt .='>';
     $txt .='</div></div>';
     
     return $txt;
	
}

function frmdate($name,$option){

	$txt='<div class="field';
	if(array_key_exists('required', $option)):
	if($option['required']===true){
		$txt.=' required';
	}
	endif;
	$txt .='">';
	$txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
	$txt .='<div class="control">';
	$txt .='<input type="date" class="input-text _has-datepicker" id="fd_date_'.$name.'" name="'.$name.'" ';
	if(array_key_exists('required', $option)):
	if($option['required']===true){
		$txt.=' required=""';
	}
	endif;

	if(array_key_exists('value', $option)):
	if(!empty($option['value'])){
		$txt.=' value="'.$option['value'].'"';
	}
	endif;

	$txt .='>';
	$txt .='</div></div>';
	
	$txt.='  <script>
			require([
			"jquery",
			"jquery/jquery-ui"
	],
   function($) {
    $("#fd_date_'.$name.'" ).datepicker({"dateFormat":"yy-mm-dd"});
  } );
  </script>';
	
	
	 
	return $txt;

}

function frmtextarea($name,$option){
	$txt='<div class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<textarea rows="3" cols="5" class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;	 
	 
	 $txt .='></textarea>';
     $txt .='</div></div>';
     
     return $txt;
}

function frmselect($name,$option){
	
	 $txt='<div class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<select class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;
	 
	$txt .='>';
	 
	 
	 
	 if(array_key_exists('options', $option)):
		foreach($option['options'] as $k=>$v){
		 	 $txt .='<option value="'.$v.'">'.$k.'</option>';
		 }
	 endif; 
		 
	 $txt .='</select>';
	 
	  	 
     $txt .='</div></div>';
     
     return $txt;
	
}

function frmnselect($name,$option){
	
	 $txt='<div class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<select class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;
	 
	 $txt .=' onchange="fdNestedOptions[\''.$name.'\'].optionChanged(this)" ';
	 
	 $txt .='>';
	 
	 
	 
	 if(array_key_exists('choices', $option)):
		foreach($option['choices'] as $k=>$v){
		 	 $txt .='<option value="'.$k.'">'.$v.'</option>';
		 }
	 endif; 
		 
	 $txt .='</select>';
	 
	  	 
     $txt .='</div></div>';
     
     return $txt;
	
}

function frmnestedselect($name,$option,$did,$fname){
	
	 $txt='<div id="'.$did.'" class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='" style="display: none;">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<select class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;
	 
	 $txt .=' onchange="fdNestedOptions[\''.$fname.'\'].optionChanged(this)" ';
	 
	 $txt .='>';
	 
	 
	 
	 if(array_key_exists('choices', $option)):
		foreach($option['choices'] as $k=>$v){
		 	 $txt .='<option value="'.$k.'">'.$v.'</option>';
		 }
	 endif; 
		 
	 $txt .='</select>';
	 
	  	 
     $txt .='</div></div>';
     
     return $txt;
	
}

function frmcheckbox($name,$option){
	$txt='<div class="field';
	 if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required';
		 }
	 endif;
	 $txt .='">';
     $txt .= '<label for="'.$name.'" class="label"><span>'.$option['label'].'</span></label>';
     $txt .='<div class="control">';
     $txt .='<input type="checkbox" class="input-text" id="'.$name.'" name="'.$name.'" ';
     if(array_key_exists('required', $option)):
		 if($option['required']===true){
		 	$txt.=' required=""';
		 }
	 endif;
	 
	 if(array_key_exists('value', $option)):
		 if(!empty($option['value'])){
		 	$txt.=' value="'.$option['value'].'"';
		 }
	 endif;
	 
	 $txt .='>';
     $txt .='</div></div>';
     
     return $txt;
}

function ticketfields(){

	$allfields=$this->getAvailableFields();
$fieldset=array();

foreach($allfields as $k=>$v){

$textoption=array();
$options='';
$name = $v['name'];

switch($v['type']){

	case 'custom_checkbox':
	$type="checkbox";
	break;
	case 'custom_paragraph':
	$type="textarea";
	break;

	case 'custom_date':
	$type="date";
	
	break;

	default:
	$type='text';

}
if(array_key_exists('choices',$v)){
	$type='select';
	$options=$v['choices'];
}

if($name=="description"){
	$type="textarea";
	//$type="editor";
}

$textoption['name']=$name;
$textoption['label']=$v['label_for_customers'];
$textoption['title']=$v['description'];

if($v['required_for_customers']==1){
	$textoption['required']=true;
}

if($name=="status"){
	$textoption['required']=true;
}

if($name=="priority"){
	$textoption['required']=true;
	$textoption['value']=1;
}



if(array_key_exists('choices',$v)){

	if($name=='status'){
		//$chopt=array(''=>'--');
		foreach($v['choices'] as $fk=>$fv){
			$chopt[$fk]=$fv[0];
		}
		$textoption['options']=$chopt;
	}
	elseif($name=='ticket_type'){
		$chopt2=array(''=>'--');
		foreach($v['choices'] as $fv2){
		$chopt2[$fv2]=$fv2;
		}
		$textoption['options']=$chopt2;
	}
	else{

		if($v['type']=='nested_field'){
			
			$textoption['options']['choices']=$v['choices'];
			
			foreach($v['nested_ticket_fields'] as $nf){
				$textoption['options']['nested'][$nf['level']]=array('name'=>$nf['name'],'label'=>$nf['label']);
			}			
			
		
		}else{
			
			$chopt3=array(''=>'--');
			
				foreach($v['choices'] as $fk=>$fv){
					$chopt3[$fv]=$fk;
				}
			$textoption['options']=$chopt3;
		}
	}

}

if($name=="requester"){
$textoption['class'] = 'validate-email';
}

if($v['type']=='nested_field'){
	$type='nselect';
	$fieldset[$name]=array('type'=>$type,'options'=>$textoption);
	
}else{


$fieldset[$name]=array('type'=>$type,'options'=>$textoption);

}

}

unset($fieldset['requester'],$fieldset['status'],$fieldset['priority'],$fieldset['ticket_type'],$fieldset['agent'],$fieldset['source'],$fieldset['group']);

	return $fieldset;
}




}
