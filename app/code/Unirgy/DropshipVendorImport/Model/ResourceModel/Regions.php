<?php

namespace Unirgy\DropshipVendorImport\Model\ResourceModel;

use Magento\Directory\Model\ResourceModel\Region\Collection;

class Regions extends Collection
{
    public function toOptionHash()
    {
        return $this->_toOptionHash('region_id', 'default_name');
    }
}