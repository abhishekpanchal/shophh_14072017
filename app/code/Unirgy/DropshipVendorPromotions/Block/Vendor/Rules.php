<?php

namespace Unirgy\DropshipVendorPromotions\Block\Vendor;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Helper\Data as HelperData;

class Rules extends Template
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var RuleFactory
     */
    protected $_ruleFactory;

    public function __construct(Context $context, 
        Registry $frameworkRegistry, 
        HelperData $helperData,
        RuleFactory $modelRuleFactory,
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;
        $this->_hlp = $helperData;
        $this->_ruleFactory = $modelRuleFactory;

        parent::__construct($context, $data);
    }

    protected $_collection;
    protected $_oldStoreId;
    protected $_unregUrlStore;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        if (!$this->_coreRegistry->registry('url_store')) {
            $this->_unregUrlStore = true;
            $this->_coreRegistry->register('url_store', $this->_storeManager->getStore());
        }
        $this->_oldStoreId = $this->_storeManager->getStore()->getId();
        $this->_storeManager->setCurrentStore(0);

        if ($toolbar = $this->getLayout()->getBlock('udpromo.grid.toolbar')) {
            $toolbar->setCollection($this->getRulesCollection());
        }

        return $this;
    }

    protected function _getUrlModelClass()
    {
        return 'core/url';
    }
    public function getUrl($route = '', $params = [])
    {
        if (!isset($params['_store']) && $this->_oldStoreId) {
            $params['_store'] = $this->_oldStoreId;
        }
        return parent::getUrl($route, $params);
    }

    protected function _afterToHtml($html)
    {
        if ($this->_unregUrlStore) {
            $this->_unregUrlStore = false;
            $this->_coreRegistry->unregister('url_store');
        }
        $this->_storeManager->setCurrentStore($this->_oldStoreId);
        return parent::_afterToHtml($html);
    }

    protected function _applyRequestFilters($collection)
    {
        /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate */
        $localeDate = $this->_hlp->getObj('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $datetimeFormatInt = \Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT;
        $dateFormat = $localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $r = $this->_request;
        $param = $r->getParam('filter_rule_name');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('name', ['like'=>'%'.$param.'%']);
        }
        $param = $r->getParam('filter_coupon_code');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('code', ['like'=>'%'.$param.'%']);
        }
        $param = $r->getParam('filter_rule_status');
        if (!is_null($param) && $param!=='') {
            $collection->addFieldToFilter('is_active', $param);
        }
        if (($v = $r->getParam('filter_rule_date_from'))) {
            $collection->addFieldToFilter('from_date', ['gteq'=>$this->_hlp->dateLocaleToInternal($v, null, true)]);
        }
        if (($v = $r->getParam('filter_rule_date_to'))) {
            $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
            $_filterDate = $localeDate->date($_filterDate, null, false);
            $_filterDate->add(new \DateInterval('P1D'));
            $_filterDate->sub(new \DateInterval('PT1S'));
            $_filterDate = datefmt_format_object($_filterDate, $datetimeFormatInt);
            $collection->addFieldToFilter('from_date', ['lteq'=>$_filterDate]);
        }
        if (($v = $r->getParam('filter_rule_expire_from'))) {
            $collection->addFieldToFilter('to_date', ['gteq'=>$this->_hlp->dateLocaleToInternal($v, null, true)]);
        }
        if (($v = $r->getParam('filter_rule_expire_to'))) {
            $_filterDate = $this->_hlp->dateLocaleToInternal($v, $dateFormat, true);
            $_filterDate = $localeDate->date($_filterDate, null, false);
            $_filterDate->add(new \DateInterval('P1D'));
            $_filterDate->sub(new \DateInterval('PT1S'));
            $_filterDate = datefmt_format_object($_filterDate, $datetimeFormatInt);
            $collection->addFieldToFilter('to_date', ['lteq'=>$_filterDate]);
        }
        $collection->addFieldToFilter('udropship_vendor', $this->getVendor()->getId());
        return $this;
    }

    public function getVendor()
    {
        return ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendor();
    }

    public function getRulesCollection()
    {
        if (!$this->_collection) {
            $v = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendor();
            if (!$v || !$v->getId()) {
                return [];
            }
            $collection = $this->_ruleFactory->create()->getCollection();

            $this->_applyRequestFilters($collection);

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

}