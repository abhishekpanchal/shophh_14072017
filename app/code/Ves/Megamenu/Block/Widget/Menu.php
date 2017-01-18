<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Megamenu\Block\Widget;

class Menu extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Ves\Megamenu\Model\Menu
     */
    protected $_menu;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context      
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager 
     * @param \Ves\Megamenu\Helper\Data                        $helper       
     * @param \Ves\Megamenu\Model\Menu                         $menu         
     * @param array                                            $data         
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Ves\Megamenu\Helper\Data $helper,
        \Ves\Megamenu\Model\Menu $menu,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
        ) {
        parent::__construct($context);
        $this->_helper          = $helper;
        $this->_menu            = $menu;
        $this->setTemplate("widget/menu.phtml");
        $this->_customerSession = $customerSession;
    }
    public function _toHtml(){
        $startTime = microtime(true);
        $store = $this->_storeManager->getStore();
        $html = $menu = '';
        if ($menuId = $this->getData('id')) {
            $menu = $this->_menu->setStore($store)->load((int)$menuId);
            if ($menu->getId() != $menuId) {
                return;
            }
        }elseif($alias = $this->getData('alias')){
            $menu = $this->_menu->setStore($store)->load(addslashes($alias));
            if ($menu->getAlias() != $alias) {
                return;
            }
        }
        if($menu){
            $customerGroups = $menu->getData('customer_group_ids');
            $customerGroupId = (int)$this->_customerSession->getCustomerId();
            if(!in_array($customerGroupId, $customerGroups)) return;
        }
        if($menu && $menu->getStatus()){
            $this->setData("menu", $menu);
        }
        return parent::_toHtml();
    }
}