<?php

namespace Bluebadger\Coupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

/**
 * Class Config
 * @package Bluebadger\Coupon\Helper
 */
class Config extends AbstractHelper
{
    const XML_PATH_GENERAL_ENABLED = 'coupon/general/enabled';
    const XML_PATH_GENERAL_RULE = 'coupon/general/rule';

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    protected $coupon;

    /**
     * Config constructor.
     * @param Context $context
     * @param \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepositoryInterface
     * @param \Magento\SalesRule\Model\Coupon $coupon
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepositoryInterface,
        \Magento\SalesRule\Model\Coupon $coupon
    )
    {
        parent::__construct($context);
        $this->coupon = $coupon;
    }

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

    /**
     * @return string
     */
    public function getCouponCode()
    {
        $ruleId = $this->getRuleId();
        $coupon = $this->coupon->loadPrimaryByRule($ruleId);

        return $coupon->getCode();
    }
}