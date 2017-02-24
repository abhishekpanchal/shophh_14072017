<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ImportStockpo;

use Magento\Framework\DataObject;

abstract class AbstractImportStockpo extends DataObject
{
    abstract public function init();
    abstract public function parse($content);
    abstract public function process($rows);
    abstract public function getImportFields();

    public function import($content)
    {
        $this->init();
        $rows = $this->parse($content);
        $this->process($rows);
        return $this;
    }
    
    public function getVendor()
    {
        return $this->getBatch()->getVendor();
    }

    public function getVendorId()
    {
        return $this->getVendor()->getId();
    }
}