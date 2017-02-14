<?php

namespace Bg\Freshdesk\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

const DATA_FRESHDESK_DOMAIN="freshdesk_section/account/text_domainname";
const DATA_FRESHDESK_ADMIN_EMAIL="freshdesk_section/account/text_email";
const DATA_FRESHDESK_ADMIN_API="freshdesk_section/account/text_api";

const DATA_FRESHDESK_ORDERID="freshdesk_section/orderid/text_orderid";
const DATA_FRESHDESK_CONTACTYN="freshdesk_section/channel/dropdown_contact";
const DATA_FRESHDESK_WIDGETYN="freshdesk_section/channel/dropdown_feedback";

const DATA_FRESHDESK_SUPPORTYN="freshdesk_section/channel/dropdown_support";
const DATA_FRESHDESK_WIDGETSCRIPT="freshdesk_section/channel/textarea_widget";

const DATA_FRESHDESK_CUSTOMER_TICKETYN="freshdesk_section/customer/dropdown_ticket";
const DATA_FRESHDESK_CUSTOMER_RECENTYN="freshdesk_section/customer/dropdown_recent";

const DATA_FRESHDESK_CUSTOMER_SSOYN="freshdesk_section/ssogroup/dropdown_sso";
const DATA_FRESHDESK_CUSTOMER_SSOKEY="freshdesk_section/ssogroup/text_ssokey";

const DATA_FRESHDESK_SSO_CNAME="freshdesk_section/ssogroup/text_ssocname";
const DATA_FRESHDESK_SSO_URL="freshdesk_section/ssogroup/text_ssoacceptableurl";


const URL_FRESHDESK_ACTION_TICKETS="api/v2/tickets";
const URL_FRESHDESK_ACTION_TICKETFILELDS="api/v2/ticket_fields";
const URL_FRESHDESK_ACTION_CONTACTS="api/v2/contacts";
const URL_FRESHDESK_ACTION_AGENTS="api/v2/agents";


    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    ) {
        parent::__construct($context);
        $this->_backendUrl = $backendUrl;
        $this->_storeManager = $storeManager;
        $this->_timezoneInterface = $timezoneInterface;
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

         


public function getConfig($config_path)
{
    return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
}

public function getfddomain()
    {
        $domain=$this->getConfig(self::DATA_FRESHDESK_DOMAIN);
		$dhost = parse_url($domain,PHP_URL_HOST);
		$domain="https://".$dhost."/";
		return $domain;

    }
    
    function gettimezone(){
    	return $this->getConfig('general/locale/timezone');
    }

public function getfdapi()
    {
        return $this->getConfig(self::DATA_FRESHDESK_ADMIN_API);

    }

public function getfdemail()
    {
        return $this->getConfig(self::DATA_FRESHDESK_ADMIN_EMAIL);

    }

public function getfdwidgetyn()
    {
        return $this->getConfig(self::DATA_FRESHDESK_WIDGETYN);

    }

public function getfdsupportyn()
    {
        return $this->getConfig(self::DATA_FRESHDESK_SUPPORTYN);

    }

public function getfdwidget()
{
	return $this->getConfig(self::DATA_FRESHDESK_WIDGETSCRIPT);
}

public function getfdcontactyn()
    {
        return $this->getConfig(self::DATA_FRESHDESK_CONTACTYN);

    }

public function getfdcustomerticket()
    {
        return $this->getConfig(self::DATA_FRESHDESK_CUSTOMER_TICKETYN);

    }

public function getfdrecent()
    {
        return $this->getConfig(self::DATA_FRESHDESK_CUSTOMER_RECENTYN);

    }

public function getfdsso()
    {
        return $this->getConfig(self::DATA_FRESHDESK_CUSTOMER_SSOYN);

    }

public function getfdssokey()
    {
        return $this->getConfig(self::DATA_FRESHDESK_CUSTOMER_SSOKEY);

    }
    
public function getfdssocname()
    {
        return $this->getConfig(self::DATA_FRESHDESK_SSO_CNAME);

    }
    
public function getfdssourl()
    {
        return $this->getConfig(self::DATA_FRESHDESK_SSO_URL);

    }
    

public function getfdorderid()
    {
        $orderid = $this->getConfig(self::DATA_FRESHDESK_ORDERID);
	/*$oid=trim(strtolower($orderid));
	$id = preg_replace('/\s+/', '_', $oid);
	return $id;*/
        return $orderid;

    }

public function getfddashboard()
{
	$domain=$this->getfddomain();
	$url=$domain."helpdesk/dashboard";
	return $url;
}

public function getfdlogin()
{
	$domain=$this->getfddomain();
	$url=$domain."login/normal";
	return $url;
}

public function getfdcustomerhome()
{
	$domain=$this->getfddomain();
	$url=$domain."support/tickets";
	return $url;
}

public function curlget($action,$data){

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

$result = html_entity_decode($result);
$result=json_decode($result);

}

public function curlpost($action,$data){

}

function getSSOUrl($strName, $strEmail, $url='') {

if($this->getfdsso()){
$secret=$this->getfdssokey();
$domain=$this->getfddomain();
$timestamp = time();
$to_be_hashed = $strName . $secret . $strEmail . $timestamp;
$hash = hash_hmac('md5', $to_be_hashed, $secret);
return $domain."login/sso/?name=".urlencode($strName)."&email=".urlencode($strEmail)."&timestamp=".$timestamp."&hash=".$hash."&redirect_to=".urlencode($url);
}
else
{
return $url;
}
	
}


}
