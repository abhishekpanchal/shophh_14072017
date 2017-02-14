<?php

namespace Bg\Freshdesk\Block\Adminhtml\System\Config;

class Implementcode extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
    	$fieldId = $element->getId();
    	
        return '
		<div class="" id="'.$fieldId.'">
		        <div class="messages">
		            <div class="message" style="margin-top: 10px;">
		                <strong>'.__('SSO Login URL ').'</strong><br />
		                '.$this->_storeManager->getStore()->getBaseUrl().'freshdesk/sso/login
		               
		            </div>
		            
		             <div class="message" style="margin-top: 10px;">
		                <strong>'.__('SSO Logout URL ').'</strong><br />
		                '.$this->_storeManager->getStore()->getBaseUrl().'freshdesk/sso/logout
		               
		            </div>
		            
		            
		        </div>
		</div>';
    }
}
