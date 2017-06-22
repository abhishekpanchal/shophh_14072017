<?php
namespace Bluebadger\Dropship\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 * @package Bluebadger\Dropship\Helper\Config
 */
class Config extends AbstractHelper
{
    const XML_PATH_SPECIFICCOUNTRY = 'carriers/dropship/specificcountry';
    const XML_PATH_SPECIFICCARRIER = 'carriers/dropship/specificcarrier';
    const KEY_STORE = 'store';

    /**
     * @return array
     */
    public function getSpecificCountry()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SPECIFICCOUNTRY, self::KEY_STORE);
    }
}