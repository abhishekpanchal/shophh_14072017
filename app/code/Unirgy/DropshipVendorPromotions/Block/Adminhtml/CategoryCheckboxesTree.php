<?php

namespace Unirgy\DropshipVendorPromotions\Block\Adminhtml;

use Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree;

class CategoryCheckboxesTree extends Tree
{
    public function getLoadTreeUrl($expanded=null)
    {
        $params = ['_current'=>true, 'id'=>null,'store'=>null];
        if ($expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('udpromo/vendor/categoriesJson', $params);
    }
}