<?php

namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;
use Magento\Framework\App\Action\Action;

class Edit extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{
       
    public function execute()
    {
        $id = $this->getRequest()->getParam('ticket_id');
        $storeViewId = $this->getRequest()->getParam('store');
        
 	$domain=$this->getAvailableFields();
	$this->_coreRegistry->register('fdfields', $domain);

        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }

function getAvailableFields()
    {


	$password = "x";

	$domain=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfddomain();

	$url = $domain."api/v2/ticket_fields";

	$api_key= $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdapi();


	$ch = curl_init($url);

	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$api_key:$password");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$info = curl_getinfo($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$headers = substr($server_output, 0, $header_size);
	$response = substr($server_output, $header_size);
	$result='';
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
