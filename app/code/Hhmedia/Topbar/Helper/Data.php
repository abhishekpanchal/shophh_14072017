<?php

namespace Hhmedia\Topbar\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

	protected $_scopeConfig;

	public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig){
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

    public function getBgcolor()
    {
        $text = $this->_scopeConfig->getValue('topbar/info/bgcolor', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $text;
    }

    public function getMenuLink()
    {
        $link = $this->_scopeConfig->getValue('awpromotions/general/link', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $link;
    }

    public function getMenuSubtitle()
    {
        $subtitle = $this->_scopeConfig->getValue('awpromotions/general/subtitle', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $subtitle;
    }

    public function getMenuTitle()
    {
        $title = $this->_scopeConfig->getValue('awpromotions/general/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $title;
    }

    public function getImage()
    {
        $image = $this->_scopeConfig->getValue('awpromotions/general/upload_image_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $image;
    }

}