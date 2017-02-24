<?php

namespace Hhmedia\Topbar\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	protected $_scopeConfig;

	public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ){
        $this->_scopeConfig = $scopeConfig;
    }

    public function getEnabled()
    {
        $enabled = $this->_scopeConfig->getValue('topbar/info/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $enabled;
    }

    public function getText()
    {
        $text = $this->_scopeConfig->getValue('topbar/info/text', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $text;
    }
}