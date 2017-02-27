<?php
namespace Bg\Freshdesk\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Psr\Log\LoggerInterface as Logger;
use \Magento\Framework\App\Action\Action;
use Bg\Freshdesk\Helper\Data as Helper;

class Contact implements ObserverInterface
{
    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @param Logger $logger
     */
    /*public function __construct(
        Logger $logger
    ) {
        $this->_logger = $logger;
    }*/

public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
Helper $helper
    ) {
        //parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
$this->_helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
    	$allfields = $this->getAvailableFields();
    	    	    	
	    foreach($allfields as $k=>$v){
	    	$name = $v['name'];
			if(empty($v['default'])):
		    if($v['required_for_customers']==1){
		    switch($v['type']){

				case 'custom_number':
					$v2=0;
				$data1['custom_fields'][$name]=(int)$v2;
				break;
				
				case 'custom_decimal':
					$v2='0.0';
				$data1['custom_fields'][$name]=(float)$v2;
				break;
				
				case 'custom_date':
					$v2='NOW()';
				$data1['custom_fields'][$name]=date('Y-m-d',strtotime($v2));
				break;
				
				default:
					$v2='-';
				$data1['custom_fields'][$name]=$v2;
				}
				
			}
			endif;
		}
        
$data = $observer->getData();
$post = $data['controller_action']->getRequest()->getPost();

$data1['email']=$post['email'];
$data1['description']=$post['comment'];


 $data1['subject'] = "Contact Us from ".$post['name'];

 $data1['status'] = 2;
 $data1['priority'] = 1;

if($this->_helper->getfdcontactyn()):

$domain=$this->_helper->getfddomain();

$api_key =$this->_helper->getfdapi();
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

endif;


    }
    
function getAvailableFields()
    {


	$password = "x";

	$domain=$this->_helper->getfddomain();

	$url = $domain."api/v2/ticket_fields";

	$api_key= $this->_helper->getfdapi();


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


