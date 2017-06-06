<?php

namespace Unirgy\DropshipVendorImport\Model\ResourceModel;

use Magento\Directory\Model\ResourceModel\Country\Collection;

class Countries extends Collection
{
    public function toOptionHash()
    {
        $countries = $this->_toOptionHash('country_id', 'name');
        $translated = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Framework\Locale\TranslatedLists');
        foreach ($countries as $ck=>&$ctr) {
            $ctr = !empty($ctr) ? $ctr : $translated->getCountryTranslation($ck);
        }
        unset($ctr);
        return $countries;
    }
}