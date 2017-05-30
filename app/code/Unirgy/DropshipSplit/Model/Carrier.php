<?php

namespace Unirgy\DropshipSplit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Unirgy\DropshipSplit\Helper\ProtectedCode;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\Item;
use Unirgy\Dropship\Helper\ProtectedCode as HelperProtectedCode;
use Unirgy\Dropship\Model\RateResultFactory;
use Zend\Json\Json;

class Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * @var ProtectedCode
     */
    protected $_splitHlpPr;

    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var HelperProtectedCode
     */
    protected $_hlpPr;

    /**
     * @var \Unirgy\Dropship\Model\Source
     */
    protected $_src;

    /**
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var RateResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateResultMethodFactory;

    /**
     * @var Item
     */
    protected $_helperItem;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory, 
        LoggerInterface $logger, 
        ProtectedCode $helperProtectedCode, 
        HelperData $helperData, 
        HelperProtectedCode $dropshipHelperProtectedCode,
        \Unirgy\Dropship\Model\Source $modelSource,
        ManagerInterface $eventManagerInterface,
        RateResultFactory $modelRateresultFactory,
        MethodFactory $rateResultMethodFactory, 
        Item $helperItem, 
        array $data = [])
    {
        $this->_splitHlpPr = $helperProtectedCode;
        $this->_hlp = $helperData;
        $this->_hlpPr = $dropshipHelperProtectedCode;
        $this->_src = $modelSource;
        $this->_eventManager = $eventManagerInterface;
        $this->_rateResultFactory = $modelRateresultFactory;
        $this->_rateResultMethodFactory = $rateResultMethodFactory;
        $this->_helperItem = $helperItem;

        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }


    protected $_code = 'udsplit';

    protected $_methods = [];
    protected $_allowedMethods = [];

    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $hl = $this->_hlp;
        $hlp = $this->_hlpPr;
        $carrierNames = $this->_src->getCarriers();

        // prepare data
        $items = $request->getAllItems();
        if (empty($items)) return;

        try {
            $hlp->prepareQuoteItems($items);
            $hlp->fixQuoteItemsWeight($items);
        } catch (\Exception $e) {
            $this->_hlp->addMessageOnce($e->getMessage());
            return;
        }

        foreach ($items as $item) {
            $quote = $item->getQuote();
            break;
        }
        $address = $quote->getShippingAddress();
        foreach ($items as $item) {
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }

        $request->setOrigPackageValueWithDiscount($request->getPackageValueWithDiscount());
        $this->_eventManager->dispatch('udsplit_carrier_collect_before', ['request'=>$request, 'address'=>$address]);

        $requests = $hlp->getRequestsByVendor($items, $request);

        $evTransport = new DataObject(['requests'=>$requests]);
        $this->_eventManager->dispatch('udsplit_carrier_process_vendor_requests', ['transport'=>$evTransport, 'address'=>$address]);
        $requests = $evTransport->getRequests();

#foreach ($requests as $r) var_dump($r); exit;

        // get available dropship shipping methods
        $shipping = $hl->getShippingMethods();

        $freeMethods = explode(',', $this->_scopeConfig->getValue('carriers/udropship/free_method', ScopeInterface::SCOPE_STORE, $hlp->getStore()));
        if ($freeMethods) {
            $_freeMethods = [];
            foreach ($freeMethods as $freeMethod) {
                if (is_numeric($freeMethod)) {
                    if ($shipping->getItemById($freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                } else {
                    if ($shipping->getItemByColumnValue('shipping_code', $freeMethod)) {
                        $_freeMethods[] = $freeMethod;
                    }
                }
                $_freeMethods[] = $freeMethod;
            }
            $freeMethods = $_freeMethods;
        }

        $result = $this->_rateResultFactory->create();
        foreach ($requests as $vId=>$vRequests) {
            $vendor = $hl->getVendor($vId);
            $systemMethods = $hl->getMultiSystemShippingMethodsByProfile($vendor);
            $vMethods = $vendor->getShippingMethods();
            foreach ($vRequests as $cCode=>$req) {
                $vResult = $hlp->collectVendorCarrierRates($req);
                $vRates = $vResult->getAllRates();
                udDump($vRates, '$vRates$vRates');
                foreach ($vRates as $rate) {
                    $wildcardUsed = false;
                    $hasVendorMethod = false;
                    $smArray = @$systemMethods[$rate->getCarrier()][$rate->getMethod()];
                    if (!empty($smArray)) {
                        foreach ($smArray as $udMethod) {
                            $hasVendorMethod = $hasVendorMethod || !empty($vMethods[$udMethod->getShippingId()]);
                        }
                    }
                    if (!$hasVendorMethod) {
                        if (!empty($systemMethods[$rate->getCarrier()]['*'])) {
                            $wildcardUsed = true;
                        } else {
                            continue;
                        }
                    }
                    if ($wildcardUsed) {
                        $smArray = $systemMethods[$rate->getCarrier()]['*'];
                    }
                    foreach ($smArray as $udMethod) {
                        $udMethod->useProfile($vendor);
                        if (empty($vMethods[$udMethod->getShippingId()])) {
                            continue;
                        }

                    foreach ($vMethods[$udMethod->getId()] as $vMethod) {

                        $_isSkippedShipping = new DataObject(['result'=>false]);
                        $this->_eventManager->dispatch('udropship_vendor_shipping_check_skipped', [
                            'shipping'=>$udMethod,
                            'address'=>$address,
                            'vendor'=>$vendor,
                            'request'=>$req,
                            'result'=>$_isSkippedShipping
                        ]);

                        if ($_isSkippedShipping->getResult()) {
                            continue;
                        }

                        if ($freeMethods
                            && $this->_scopeConfig->isSetFlag('carriers/udropship/free_shipping_allowed', ScopeInterface::SCOPE_STORE, $request->getStoreId())
                            && $this->_scopeConfig->isSetFlag('carriers/udropship/freeweight_allowed', ScopeInterface::SCOPE_STORE, $request->getStoreId())
                            && $this->isRuleFreeshipping($req)
                            && in_array($udMethod->getShippingCode(), $freeMethods)
                        ) {
                            $rate->setPrice(0);
                            $rate->setIsFwFreeShipping(true);
                        }

                        $rate->setPrice($this->getMyMethodPrice($rate->getPrice(), $req, $udMethod->getShippingCode()));

                        $rate->setUdsIsSkip(false);
                        $this->_eventManager->dispatch('udropship_process_vendor_carrier_single_rate_result', [
                            'vendor_method'=>$vMethod,
                            'udmethod'=>$udMethod,
                            'address'=>$address,
                            'vendor'=>$vendor,
                            'request'=>$req,
                            'rate'=>$rate,
                        ]);

                        if ($rate->getUdsIsSkip()) {
                            continue;
                        }

                        $vendorCode = $vendor->getCarrierCode();
                        if ($req->getForcedCarrierFlag()) {
                            $ecCode = $ocCode = $rate->getCarrier();
                        } else {
                            $ecCode = !empty($vMethod['est_carrier_code'])
                                ? $vMethod['est_carrier_code']
                                : (!empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode);
                            $ocCode = !empty($vMethod['carrier_code']) ? $vMethod['carrier_code'] : $vendorCode;
                        }
                        $oldEstCode = null;
                        $resultKey = sprintf('%s-%s', $vId, $udMethod->getShippingCode());
                        if (!empty($resultRates[$resultKey])) {
                            $oldEstCode = $resultRates[$resultKey]->getUdEstCarrier();
                            if ($this->_hlp->isUdsprofileActive()
                                && $resultRates[$resultKey]->getUdsprofileSortOrder()<$vMethod['sort_order']
                            ) {
                                continue;
                            }
                        }
                        if ($ecCode!=$rate->getCarrier()) {
                            if (!$wildcardUsed && $vendor->getUseRatesFallback()
                                && !$this->_hlp->isUdsprofileActive()
                            ) {
                                if ($oldEstCode==$ecCode) {
                                    continue;
                                } elseif ($oldEstCode!=$ocCode && $ocCode==$rate->getCarrier()) {
                                    $ecCode = $ocCode;
                                } elseif (!$oldEstCode && $vendorCode==$rate->getCarrier()) {
                                    $ecCode = $vendorCode;
                                } else {
                                    continue;
                                }
                            } else {
                                continue;
                            }
                        }
                        if ('**estimate**' == $ocCode) {
                            $ocCode = $ecCode;
                        }
                        if ($wildcardUsed && $ecCode!=$ocCode) {
                            continue;
                        }
                        if ($ecCode!=$rate->getCarrier()) {
                            continue;
                        }
                        if ($this->_hlp->isUdsprofileActive()) {
                            $codeToCompare = $vMethod['carrier_code'].'_'.$vMethod['method_code'];
                            if (!empty($vMethod['est_use_custom'])) {
                                $codeToCompare = $vMethod['est_carrier_code'].'_'.$vMethod['est_method_code'];
                            }
                            if ($codeToCompare!=$rate->getCarrier().'_'.$rate->getMethod()) {
                                continue;
                            }
                        }
                        if ($ocCode!=$ecCode) {
                            $ocMethod = $udMethod->getSystemMethods($ocCode);
                            if ($this->_hlp->isUdsprofileActive()) {
                                $ocMethod = $vMethod['method_code'];
                            }
                            if (empty($ocMethod)) {
                                continue;
                            }
                            $methodNames = $hl->getCarrierMethods($ocCode);
                            $rate
                                ->setCarrier($ocCode)
                                ->setMethod($ocMethod)
                                ->setCarrierTitle($carrierNames[$ocCode])
                                ->setMethodTitle($methodNames[$ocMethod])
                            ;
                        }
                        $rate->setPriority(@$vMethod['priority'])
                            ->setUdEstCarrier($ecCode)
                            ->setUdVendorMethod($vMethod)
                            ->setUdVid($vId)
                            ->setSystemMethodTitle($rate->getMethodTitle())
                            ->setUdropshipTitle($udMethod->getStoreTitle($quote->getStoreId()))
                            ->setUdropshipShippingId($udMethod->getShippingId());

                        if ($this->getConfigData('use_dropship_titles')) {
                            $rate->setMethodTitle($rate->getUdropshipTitle());
                        }

                        if ($this->_hlp->isUdsprofileActive()) {
                            $rate->setUdsprofileSortOrder($vMethod['sort_order']);
                        }

                        if ($wildcardUsed) {
                            $resultKey .= $rate->getCarrier().'_'.$rate->getMethod();
                        }
                        $resultRates[$resultKey] = $rate;
                        break;
                    }
                    }
                    foreach ($smArray as $udMethod) {
                        $udMethod->resetProfile();
                    }
                }
            }
        }

        if (!empty($resultRates)) {
            foreach ($resultRates as $resultRate) {
                $result->append($resultRate);
                if ($exRate = $hl->getExtraChargeRate($req, $resultRate, $resultRate->getUdVid(), $resultRate->getUdVendorMethod())) {
                    $result->append($exRate);
                }
            }
        }

        foreach ($items as $item) {
            $quote = $item->getQuote();
            break;
        }
        if (empty($quote)) {
            $result->append($hlp->errorResult('udsplit'));
            return $result;
        }

        $address = $quote->getShippingAddress();
        foreach ($items as $item) {
            if ($item->getAddress()) {
                $address = $item->getAddress();
            }
            break;
        }

        $cost = 0;
        $price = 0;
        $details = $address->getUdropshipShippingDetails();
        $customerSelected = $methodCodes = [];
        if ($details && ($details = $this->_hlp->unserializeArr($details)) && !empty($details['methods'])) {
            foreach ($details['methods'] as $vId=>$rate) {
                if (!empty($rate['code'])) {
                    $methodCodes[$vId] = $rate['code'];
                    $customerSelected[$vId] = @$rate['customer_selected'];
                }
            }
        }

        $totalMethod = $this->_scopeConfig->getValue('udropship/customer/estimate_total_method', ScopeInterface::SCOPE_STORE);

        $details = ['version' => $this->_hlp->getVersion()];
        $result->sortRatesByPriority();
        $rates = $result->getAllRates();
        $hasDefPatternMatch = [];
        foreach ($rates as $rate) {
            if ($rate->getErrorMessage()) {
                continue;
            }
            $vId = $rate->getUdropshipVendor();
            $vendor = $hl->getVendor($vId);
            if (!$vId) {
                continue;
            }
            $pattern = $vendor->getDefaultShippingMethodPattern();
            if (!isset($hasDefPatternMatch[$vId])) {
                $hasDefPatternMatch[$vId] = false;
            }
            $hasDefPatternMatch[$vId] = $hasDefPatternMatch[$vId] || ($pattern ? preg_match('#'.preg_quote($pattern).'#i', $rate->getMethodTitle()) : true);
        }
        $matchRank = [];
        $costsByVid = $pricesByVid = [];
        foreach ($rates as $rate) {
            if ($rate->getErrorMessage()) {
                continue;
            }
            $vId = $rate->getUdropshipVendor();
            $vendor = $hl->getVendor($vId);
            if (!$vId) {
                continue;
            }
            $code = $rate->getCarrier().'_'.$rate->getMethod();
            $data = [
                'code' => $code,
                'cost' => (float)$rate->getCost(),
                'price' => (float)$rate->getPrice(),
                'cost_excl' => (float)$this->getShippingPrice($rate->getCost(), $vendor, $address, 'base'),
                'cost_incl' => (float)$this->getShippingPrice($rate->getCost(), $vendor, $address, 'incl'),
                'price_excl' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'base'),
                'price_incl' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'incl'),
                'cost_tax' => (float)$this->getShippingPrice($rate->getCost(), $vendor, $address, 'tax'),
                'tax' => (float)$this->getShippingPrice($rate->getPrice(), $vendor, $address, 'tax'),
                'carrier_title' => $rate->getCarrierTitle(),
                'method_title' => $rate->getMethodTitle(),
                'is_free_shipping' => (int)$rate->getIsFwFreeShipping(),
                'customer_selected' => false,
            ];

            if (!isset($matchRank[$vId])) {
                $matchRank[$vId] = 0;
            }

            $pattern = $vendor->getDefaultShippingMethodPattern();
            $defPatternMatch = $pattern ? preg_match('#'.preg_quote($pattern).'#i', $rate->getMethodTitle()) : true;

            $isCurMatch = false;
            $curMatchRank = 0;

            if (!empty($methodCodes[$vId]) && $code==$methodCodes[$vId] && !empty($customerSelected[$vId])) {
                $curMatchRank = 1000;
                $isCurMatch = true;
                $data['customer_selected'] = true;
            } elseif ($vendor->getDefaultShippingId()
                && $vendor->getDefaultShippingId()==$rate->getUdropshipShippingId()
                && (!$rate->getHasExtraCharge()
                    || (bool)$vendor->getIsExtraChargeShippingDefault()==(bool)$rate->getIsExtraCharge()
                )) {
                $curMatchRank = 100;
                $isCurMatch = true;
            } elseif ($defPatternMatch) {
                $curMatchRank = 10;
                $isCurMatch = true;
            } elseif (empty($details['methods'][$vId])) {
                $curMatchRank = 1;
                $isCurMatch = true;
            }

            if ($isCurMatch && ($curMatchRank>$matchRank[$vId] || $curMatchRank==$matchRank[$vId] && $pricesByVid[$vId]>$data['price'])) {
                $matchRank[$vId] = $curMatchRank;
                // updating already chosen vendor shipping method price
                $details['methods'][$vId] = $data;
                $costsByVid[$vId] = $data['cost'];
                $pricesByVid[$vId] = $data['price'];
            }
        }

        $price = $cost = 0;
        foreach ($pricesByVid as $_vId=>$_price) {
            $price = $hl->applyEstimateTotalPriceMethod($price, $pricesByVid[$_vId]);
            $cost = $hl->applyEstimateTotalCostMethod($cost, $costsByVid[$_vId]);
        }

        if ($rates) {
            $method = $this->_rateResultMethodFactory->create();
            $method->setCarrier('udsplit');
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('total');
            $method->setMethodTitle(__('Total'));
            $method->setCost($price);
            //$method->setPrice($this->getMethodPrice($price, 'total'));
            $method->setPrice($price);
            $result->append($method);
        } else {
            $result->append($hlp->errorResult('udsplit'));
        }

        $address->setUdropshipShippingDetails(Json::encode($details));
        $address->setShippingMethod('udsplit_total');
        $address->setShippingDescription($this->getConfigData('title'));
        $address->setShippingAmount($price);

        $this->_eventManager->dispatch('udsplit_carrier_collect_after', ['request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details]);
        $this->_eventManager->dispatch('udropship_carrier_collect_after', ['request'=>$request, 'result'=>$result, 'address'=>$address, 'details'=>$details]);

        return $result;
    }

    public function getShippingPrice($baseShipping, $vId, $address, $type)
    {
        return $this->_hlp->getShippingPrice($baseShipping, $vId, $address, $type);
    }

    public function getMyMethodPrice($cost, $request, $method='')
    {
        $freeMethods = explode(',', $this->_scopeConfig->getValue('carriers/udropship/free_method', ScopeInterface::SCOPE_STORE, $request->getStoreId()));
        $freeShippingSubtotal = $this->_scopeConfig->getValue('carriers/udropship/free_shipping_subtotal', ScopeInterface::SCOPE_STORE);
        if ($freeShippingSubtotal === null || $freeShippingSubtotal === '') {
            $freeShippingSubtotal = false;
        }
        if (in_array($method, $freeMethods)
            && $this->_scopeConfig->isSetFlag('carriers/udropship/free_shipping_allowed', ScopeInterface::SCOPE_STORE, $request->getStoreId())
            && $this->_scopeConfig->isSetFlag('carriers/udropship/free_shipping_enable', ScopeInterface::SCOPE_STORE, $request->getStoreId())
            && $freeShippingSubtotal!==false
            && ($freeShippingSubtotal <= $request->getPackageValueWithDiscount() || $freeShippingSubtotal <= $request->getOrigPackageValueWithDiscount())
        ) {
            $price = '0.00';
        } else {
            $price = $this->getFinalPriceWithHandlingFee($cost);
        }
        return $price;
    }

    public function getAllowedMethods()
    {
        if (empty($this->_allowedMethods)) {
            $this->_allowedMethods = ['total'=>'Total'];
        }
        return $this->_allowedMethods;
    }

    protected function _getAllMethods()
    {

    }

    public function getUseForAllProducts()
    {
        return true;
    }

    public function isRuleFreeshipping($request)
    {
        $isFreeshipping = true;
        foreach ($request->getAllItems() as $item) {
            if ($item->getFreeShipping()!==true && $item->getTotalQty()>$item->getFreeShipping()) {
                $isFreeshipping = false;
                break;
            }
        }
        $address = $this->_helperItem->getAddress($request->getAllItems());
        if ($address instanceof DataObject && $address->getFreeShipping() === true) {
            $isFreeshipping = true;
        }
        return $isFreeshipping;
    }
}
