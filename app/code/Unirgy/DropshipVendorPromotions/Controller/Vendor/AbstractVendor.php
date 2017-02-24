<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipVendorPromotions
 * @copyright  Copyright (c) 2011-2012 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Controller\Vendor\AbstractVendor as VendorAbstractVendor;
use Unirgy\Dropship\Helper\Data as HelperData;

abstract class AbstractVendor extends VendorAbstractVendor
{
    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    protected $_helperCatalog;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig, 
        DesignInterface $viewDesignInterface, 
        StoreManagerInterface $storeManager, 
        LayoutFactory $viewLayoutFactory, 
        Registry $registry, 
        ForwardFactory $resultForwardFactory, 
        HelperData $helper, 
        PageFactory $resultPageFactory, 
        RawFactory $resultRawFactory, 
        Header $httpHeader, 
        RuleFactory $modelRuleFactory,
        CategoryFactory $modelCategoryFactory,
        \Unirgy\Dropship\Helper\Catalog $helperCatalog
    )
    {
        $this->_ruleFactory = $modelRuleFactory;
        $this->_categoryFactory = $modelCategoryFactory;
        $this->_helperCatalog = $helperCatalog;

        parent::__construct($context, $scopeConfig, $viewDesignInterface, $storeManager, $viewLayoutFactory, $registry, $resultForwardFactory, $helper, $resultPageFactory, $resultRawFactory, $httpHeader);
    }

    public function checkRule()
    {
        $ruleId = $this->_request->getParam('id');
        $vendorId = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendorId();
        $collection = $this->_ruleFactory->create()->getCollection()
            ->addFieldToFilter('rule_id', $ruleId)
            ->addFieldToFilter('udropship_vendor', $vendorId);
        $collection->load();
        if (!$collection->getFirstItem()->getId()) {
            throw new \Exception('Rule Not Found');
        }
        return $this;
    }
    protected function _redirectRuleAfterPost()
    {
        $session = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session');
        if ($session->getUdpromoLastRulesGridUrl()) {
            return $this->_response->setRedirect($session->getUdpromoLastRulesGridUrl());
        } else {
            return $this->_redirect('udpromo/vendor/rules');
        }
    }

    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $storeId    = (int) $this->getRequest()->getParam('store');

        $category   = $this->_categoryFactory->create();
        $category->setStoreId($storeId);

        if ($categoryId) {
            $category->load($categoryId);
            if ($storeId) {
                $rootId = $this->_storeManager->getStore($storeId)->getRootCategoryId();
                if (!in_array($rootId, $category->getPathIds())) {
                    $this->_redirect('*/*/', ['_current'=>true, 'id'=>null]);
                    return false;
                }
            }
        }

        $this->_registry->register('category', $category);
        $this->_registry->register('current_category', $category);

        return $category;
    }
}