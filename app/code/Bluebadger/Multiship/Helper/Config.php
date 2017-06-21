<?php
namespace Bluebadger\Multiship\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 * @package Bluebadger\Multiship\Helper\Config
 */
class Config extends AbstractHelper
{
    const XML_PATH_SPECIFICCOUNTRY = 'carriers/multiship/specificcountry';
    const XML_PATH_SPECIFICCARRIER = 'carriers/multiship/specificcarrier';
    const KEY_STORE = 'store';

    /**
     * @return array
     */
    public function getSpecificCountry()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SPECIFICCOUNTRY, self::KEY_STORE);
    }

    /**
     * @return mixed
     */
    public function getSpecificCarrier()
    {
        return 'CP,Fedex';
        //return $this->scopeConfig->getValue(self::XML_PATH_SPECIFICCARRIER, self::KEY_STORE);
    }
}