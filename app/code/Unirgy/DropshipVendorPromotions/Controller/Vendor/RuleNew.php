<?php

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;



class RuleNew extends AbstractVendor
{
    public function execute()
    {
        $this->_renderPage(null, 'udpromo');
    }
}
