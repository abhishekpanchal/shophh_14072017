<?php
namespace Bg\Freshdesk\Block;

use Bg\Freshdesk\Helper\Data as HelperData;
use Magento\Framework\View\Element\Template\Context;

class Footer extends \Magento\Framework\View\Element\Html\Link
{

protected $_template = 'Bg_Freshdesk::footerlink.phtml';
protected $_scopeConfig;
protected $_helper;

public function __construct(
Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helper    = $helperData;
        parent::__construct($context, $data);
    }

public function getHref()
{
	return __('Support');
}
public function getLabel()
{
	return __('Support');
}

function getdomain(){

return $this->getUrl('freshdesk/customer/support/');

	

}

function checksupportlink()
{
	return $this->_helper->getfdsupportyn();
}



}
