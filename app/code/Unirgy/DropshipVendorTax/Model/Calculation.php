<?php

namespace Unirgy\DropshipVendorTax\Model;

class Calculation extends \Magento\Tax\Model\Calculation
{
    protected $_vtc=[];
    public function getVendorTaxClasses($ruleId)
    {
        if (!isset($this->_vtc[$ruleId])) {
            $this->_vtc[$ruleId] = $this->getResource()->getCalculationsById('vendor_tax_class_id', $ruleId);
        }
        return $this->_vtc[$ruleId];
    }
    public function getStoreRate($request, $store = null)
    {
        $storeRequest = $this->getRateOriginRequest($store)
            ->setProductClassId($request->getProductClassId())
            ->setVendorClassId($request->getVendorClassId())
        ;
        return $this->getRate($storeRequest);
    }
    protected function _getRequestCacheKey($request)
    {
        $store = $request->getStore();
        $key = '';
        if ($store instanceof \Magento\Store\Model\Store) {
            $key = $store->getId() . '|';
        } elseif (is_numeric($store)) {
            $key = $store . '|';
        }
        $key .= $request->getProductClassId() . '|'
            . $request->getCustomerClassId() . '|'
            . $request->getVendorClassId() . '|'
            . $request->getCountryId() . '|'
            . $request->getRegionId() . '|'
            . $request->getPostcode();
        return $key;
    }
}