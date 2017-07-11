<?php

namespace Bluebadger\Coupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Config
 * @package Bluebadger\Coupon\Helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_GENERAL_ENABLED = 'coupon/general/enabled';
    const XML_PATH_GENERAL_RULE = 'coupon/general/rule';

    /**
     * Return whether or not the module is active for the current store.
     * @return bool
     */
    public function isEnabled()
    {
        return (boolean)$this->scopeConfig->getValue(self::XML_PATH_GENERAL_ENABLED);
    }

    /**
     * Get the rule ID associated with the module.
     * @return string
     */
    public function getRuleId()
    {
        return (string)$this->scopeConfig->getValue(self::XML_PATH_GENERAL_RULE);
    }
}