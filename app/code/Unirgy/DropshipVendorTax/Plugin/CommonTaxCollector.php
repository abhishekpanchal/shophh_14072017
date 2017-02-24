<?php

namespace Unirgy\DropshipVendorTax\Plugin;

class CommonTaxCollector
{
    protected $_udtaxHlp;
    public function __construct(\Unirgy\DropshipVendorTax\Helper\Data $udtaxHelper)
    {
        $this->_udtaxHlp = $udtaxHelper;
    }
    public function aroundMapItem(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject,
        \Closure $proceed,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $itemDataObjectFactory,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item,
        $priceIncludesTax,
        $useBaseCurrency,
        $parentCode=null
    )
    {
        $itemDataObject = $proceed($itemDataObjectFactory, $item, $priceIncludesTax, $useBaseCurrency, $parentCode);
        $this->_udtaxHlp->setVendorClassId($itemDataObject, $item);
        return $itemDataObject;
    }
}