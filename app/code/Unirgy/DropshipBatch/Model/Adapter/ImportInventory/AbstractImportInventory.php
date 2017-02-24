<?php

namespace Unirgy\DropshipBatch\Model\Adapter\ImportInventory;

use Magento\Framework\DataObject;

abstract class AbstractImportInventory extends DataObject
{
    abstract public function init();
    abstract public function parse($content);
    abstract public function process($rows);
    abstract public function getInvImportFields();

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
