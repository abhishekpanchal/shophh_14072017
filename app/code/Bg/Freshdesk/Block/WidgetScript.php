<?php

namespace Bg\Freshdesk\Block;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\ObjectManagerInterface;
use Bg\Freshdesk\Helper\Data as HelperData;

class WidgetScript extends Template
{
    protected $helperData;
    protected $objectFactory;
protected $_scopeConfig;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->helperData    = $helperData;
        $this->objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    public function getHelper()
    {
        return $this->helperData;
    }

function getfdwidget(){
$domain='';
if($this->helperData->getfdwidgetyn()):

$domain=$this->helperData->getfdwidget();

endif;

return $domain;

}



}
