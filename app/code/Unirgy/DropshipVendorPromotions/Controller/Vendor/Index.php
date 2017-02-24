<?php

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;



class Index extends AbstractVendor
{
    public function execute()
    {
        $this->_forward('rules');
    }
}
