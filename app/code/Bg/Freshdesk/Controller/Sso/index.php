<?php
namespace Bg\Freshdesk\Controller\Sso;

class Index extends \Magento\Framework\App\Action\Action {
	
 public function execute() {

 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$customerSession = $objectManager->get('Magento\Customer\Model\Session');
if($customerSession->isLoggedIn()) {
   
	$host=$this->getRequest()->getParam('host_url');
	$cutomerEmail =(string)$customerSession->getCustomer()->getEmail();
	$cutomerName =(string)$customerSession->getCustomer()->getName();
	
	if(!empty($host)){
		$dashboard=$host;	
	
	}else{
		$dashboard=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdcustomerhome();
	}
	
$redirect_url = $this->get_redirect_url( $dashboard );
         
         $url=$this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getSSOUrl($cutomerName,$cutomerEmail,$redirect_url);
       	 
         $this->_redirect($url);
        }
        else{
        	
        	$customerSession->setData("freshdeskredirect", 1);
              $this->_redirect('customer/account/login/');
            }


$this->_view->loadLayout();
$this->_view->renderLayout();
  }
  
function get_redirect_url($host_url) {
	$DOMAIN_REGEX= '/\bhttps?:\/\/([-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|])/i';
	$freshdesk_cname = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdssocname();
	$freshdesk_domain_url = $this->_objectManager->create('Bg\Freshdesk\Helper\Data')->getfdssourl();

	//Stripping protocols from urls to match the host url correctly.
	$host_url = preg_replace( $DOMAIN_REGEX, "$1", trim($host_url) );
	$domains = explode( ",", $freshdesk_cname );
	array_push( $domains, $freshdesk_domain_url );

	//Checking the host url against the provided helpdesk/portal url to avoid Open-redirect vulnerability
	foreach ( $domains as $domain ) {
		$domain = trim($domain);
		$url = preg_replace( $DOMAIN_REGEX, "$1", $domain);
		if ( $url == $host_url ) {
			return $domain;
		}
	}
}

}
