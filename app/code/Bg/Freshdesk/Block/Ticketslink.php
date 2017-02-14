<?php

namespace Bg\Freshdesk\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Bg\Freshdesk\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;

class Ticketslink extends Template
{
    protected $_helper;
    protected $objectFactory;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_helper    = $helperData;
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        #return $this->helperData;
    }

public function getLinkAttributes()
{

$url=$this->getUrl('freshdesk/customer/index');
$ret = 'href="'.$url.'"';
return $ret;

}

public function getCurrentUrl(){

$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
return $urlInterface->getCurrentUrl();

}

function getLiAttributes()
{

$urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
$a = $urlInterface->getCurrentUrl();
$b=$this->getUrl('freshdesk/customer/index');
if($a==$b):
return 'class="nav item current"';
else:
return 'class="nav item"';
endif;
}

function checkcustomerticket()
{
return $this->_helper->getfdcustomerticket();
}

}
