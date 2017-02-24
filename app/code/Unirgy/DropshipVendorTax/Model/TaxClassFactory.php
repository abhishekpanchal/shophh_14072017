<?php

namespace Unirgy\DropshipVendorTax\Model;

class TaxClassFactory extends \Magento\Tax\Model\TaxClass\Factory
{
    protected $_types = [
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER => 'Magento\Tax\Model\TaxClass\Type\Customer',
        \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT => 'Magento\Tax\Model\TaxClass\Type\Product',
        \Unirgy\DropshipVendorTax\Model\Source::TAX_CLASS_TYPE_VENDOR => '\Unirgy\DropshipVendorTax\Model\TaxClassTypeVendor'
    ];
}