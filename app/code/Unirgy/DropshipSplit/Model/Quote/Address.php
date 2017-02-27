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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Customer\Model\Address\Mapper;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Quote\Model\Quote\Address\CustomAttributeListInterface;
use Magento\Quote\Model\Quote\Address\ItemFactory;
use Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory;
use Magento\Quote\Model\Quote\Address\RateFactory;
use Magento\Quote\Model\Quote\Address\RateRequestFactory;
use Magento\Quote\Model\Quote\Address\TotalFactory;
use Magento\Quote\Model\Quote\Address\Total\CollectorFactory;
use Magento\Quote\Model\Quote\Address\Validator;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote\TotalsReader;
use Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory as RateCollectionFactory;
use Magento\Shipping\Model\CarrierFactoryInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipSplit\Helper\Data as DropshipSplitHelperData;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Zend\Json\Json;

class Address extends QuoteAddress
{
    /**
     * @var DropshipSplitHelperData
     */
    protected $_splitHlp;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    public function __construct(Context $context,
        Registry $registry, 
        ExtensionAttributesFactory $extensionFactory, 
        AttributeValueFactory $customAttributeFactory, 
        HelperData $directoryData, 
        Config $eavConfig, 
        AddressConfig $addressConfig, 
        RegionFactory $regionFactory, 
        CountryFactory $countryFactory, 
        AddressMetadataInterface $metadataService, 
        AddressInterfaceFactory $addressDataFactory, 
        RegionInterfaceFactory $regionDataFactory, 
        DataObjectHelper $dataObjectHelper, 
        ScopeConfigInterface $scopeConfig, 
        ItemFactory $addressItemFactory, 
        CollectionFactory $itemCollectionFactory, 
        RateFactory $addressRateFactory, 
        RateCollectorInterfaceFactory $rateCollector, 
        RateCollectionFactory $rateCollectionFactory, 
        RateRequestFactory $rateRequestFactory, 
        CollectorFactory $totalCollectorFactory, 
        TotalFactory $addressTotalFactory, 
        Copy $objectCopyService, 
        CarrierFactoryInterface $carrierFactory, 
        Validator $validator, 
        Mapper $addressMapper, 
        CustomAttributeListInterface $attributeList, 
        TotalsCollector $totalsCollector, 
        TotalsReader $totalsReader, 
        DropshipSplitHelperData $helperData,
        DropshipHelperData $dropshipHelperData, 
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_splitHlp = $helperData;
        $this->_hlp = $dropshipHelperData;

        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $directoryData, $eavConfig, $addressConfig, $regionFactory, $countryFactory, $metadataService, $addressDataFactory, $regionDataFactory, $dataObjectHelper, $scopeConfig, $addressItemFactory, $itemCollectionFactory, $addressRateFactory, $rateCollector, $rateCollectionFactory, $rateRequestFactory, $totalCollectorFactory, $addressTotalFactory, $objectCopyService, $carrierFactory, $validator, $addressMapper, $attributeList, $totalsCollector, $totalsReader, $resource, $resourceCollection, $data);
    }

    protected function _dispatchExtcoEvent($event, $args)
    {
        if ($this->_scopeConfig->isSetFlag('carriers/udsplit/extco_dispatch_events', ScopeInterface::SCOPE_STORE)) {
            $this->_eventManager->dispatch($event, $args);
        }
        return $this;
    }
    protected function __getTrace($trace=null)
    {
        return '';
        if (is_null($trace) && $this->_scopeConfig->isSetFlag('carriers/udsplit/extco_check', ScopeInterface::SCOPE_STORE)) {
            $trace = mageDebugBacktrace(1,0);
        }
        return $trace;
    }
    protected function _isGoogleCheckout($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = preg_match('/app.code.core.Mage.GoogleCheckout.Model.Api.Xml.Callback.php/', $trace)
                || preg_match('/app.code.core.Mage.GoogleCheckout.controllers.RedirectController.php/', $trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_google_checkout', ['address'=>$this, 'vars'=>['flag'=>&$flag]]);
        }
        return $flag;
    }
    protected function _isPaypalExpress($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = preg_match('/app.code.core.Mage.Paypal.Block.Express.Review.php/', $trace)
                || preg_match('/app.code.core.Mage.Paypal.Model.Express.Checkout.php/', $trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_paypal_express', ['address'=>$this, 'vars'=>['flag'=>&$flag]]);
        }
        return $flag;
    }
    protected function _isExternalCheckout($trace=null)
    {
        $flag = false;
        if (($trace = $this->__getTrace($trace))) {
            $flag = $this->_isGoogleCheckout($trace) || $this->_isPaypalExpress($trace);
            $this->_dispatchExtcoEvent('udsplit_check_is_external_checkout', ['address'=>$this, 'vars'=>['flag'=>&$flag]]);
        }
        return $flag;
    }
    public function setUdropshipShippingDetails($details)
    {
        $this->setData('udropship_shipping_details', $details);
        if ($this->_isExternalCheckout()) {
            if (($quote = $this->getQuote())) {
                $res = $quote->getResource();
                $w = $this->_hlp->rHlp()->getConnection();
                $quote->setUdropshipShippingDetails($details);
                $w->update(
                    $res->getTable('quote'),
                    ['udropship_shipping_details'=>$details],
                    $w->quoteInto($res->getIdFieldName().'=?', $quote->getId())
                );
            }
        }
        return $this;
    }
    public function getUdropshipShippingDetails()
    {
        $details = $this->getData('udropship_shipping_details');
        if (!$details && $this->_isExternalCheckout() && ($quote = $this->getQuote())) {
            $details = $quote->getData('udropship_shipping_details');
        }
        return $details;
    }
    
    public function getShippingMethod()
    {
        if (!$this->getData('shipping_method')
            && $this->getShippingRateByCode('udsplit_total')
        ) {
            $this->setData('shipping_method', 'udsplit_total');
        }
        return $this->getCountryId() ? $this->getData('shipping_method') : null;
    }

    public function setShippingMethod($method)
    {
        if (!$this->_splitHlp->isActive() || !is_array($method)) {
            $this->setData('shipping_method', $method);
            return $this;
        }

        $hl = $this->_hlp;

        $details = $this->getUdropshipShippingDetails();
        $details = $details ? $this->_hlp->unserializeArr($details) : ['version'=>$this->_hlp->getVersion()];
        $cost = 0;
        $price = 0;
        $rates = $this->getShippingRatesCollection();
        foreach ($method as $vId=>$code) {
            $r = null;
            foreach ($rates as $rate) {
                if ($rate->getUdropshipVendor()==$vId && $rate->getCode()==$code) {
                    $r = $rate;
                }
            }
            if (!$r) {
                continue;
            }
            $vendor = $hl->getVendor($vId);
            $data = [
                'code' => $code,
                'cost' => (float)$r->getCost(),
                'price' => (float)$r->getPrice(),
                'cost_excl' => (float)$this->getShippingPrice($r->getCost(), $vendor, $this, 'base'),
                'cost_incl' => (float)$this->getShippingPrice($r->getCost(), $vendor, $this, 'incl'),
                'price_excl' => (float)$this->getShippingPrice($r->getPrice(), $vendor, $this, 'base'),
                'price_incl' => (float)$this->getShippingPrice($r->getPrice(), $vendor, $this, 'incl'),
                'cost_tax' => (float)$this->getShippingPrice($r->getCost(), $vendor, $this, 'tax'),
                'tax' => (float)$this->getShippingPrice($r->getPrice(), $vendor, $this, 'tax'),
                'carrier_title' => $r->getCarrierTitle(),
                'method_title' => $r->getMethodTitle(),
                'customer_selected' => true,
            ];
            $details['methods'][$vId] = $data;
            $cost = $hl->applyEstimateTotalPriceMethod($cost, $data['cost']);
            $price = $hl->applyEstimateTotalPriceMethod($price, $data['price']);
        }

        if (empty($details['methods'])) {
            foreach ($method as $vId=>$code) {
                $details['methods'][$vId] = [
                    'code' => $code,
                    'customer_selected' => true,
                ];
            }
        }

        $this->_eventManager->dispatch('udsplit_quote_setShippingMethod_price', ['address'=>$this, 'vars'=>['price'=>&$price, 'details'=>&$details]]);

        $this->setUdropshipShippingDetails(Json::encode($details));
        $method = 'udsplit_total';
        $rate = $this->getShippingRateByCode($method);
        if ($rate) {
            $rate->setCost($cost)->setPrice($price);
        }
        $this->setData('shipping_method', $method);
        $this->setShippingDescription($this->_scopeConfig->getValue('carriers/udsplit/title', ScopeInterface::SCOPE_STORE));
        return $this;
    }

    public function getShippingPrice($baseShipping, $vId, $address, $type)
    {
        return $this->_hlp->getShippingPrice($baseShipping, $vId, $address, $type);
    }

    public function getGroupedAllShippingRates()
    {
        $qRates = parent::getGroupedAllShippingRates();
        if ($this->_splitHlp->isActive() && $this->_isPaypalExpress()) {
            $rates = [];
            foreach ($qRates as $cCode=>$cRates) {
                foreach ($cRates as $rate) {
                    $vId = $rate->getUdropshipVendor();
                    if (!$vId) {
                        $rates[$cCode][] = $rate;
                    }
                }
            }
        } else {
            $rates = $qRates;
        }
        return $rates;
    }

    public function getShippingRateByCode($code)
    {
        if (is_array($code)) {
            return true;
        }
        return parent::getShippingRateByCode($code);
    }
    
    public function __clone()
    {
        if ($this->getAddressType() == 'billing') {
            $this->unsUdropshipShippingDetails();
        }
        return parent::__clone();
    }
}