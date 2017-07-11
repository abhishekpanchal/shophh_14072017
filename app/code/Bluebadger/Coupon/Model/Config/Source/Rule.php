<?php

namespace Bluebadger\Coupon\Model\Config\Source;

use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Class Rule
 * @package Bluebadger\Coupon\Model\Config\Source
 */
class Rule implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * Rule constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
    )
    {
        $this->ruleCollectionFactory = $ruleCollectionFactory;
    }

    /**
     * Get list of rules.
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        $options = [];

        $options[] = [
            'value' => '',
            'label' => __('No rule selected')
        ];

        /** @var \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules */
        $rules = $this->ruleCollectionFactory->create();
        $rules->addFieldToSelect('name');
        $rules->addFieldToSelect('rule_id');
        $rules->addFieldToFilter('is_active', true);

        /** @var RuleInterface $rule */
        foreach ($rules as $rule) {
            $options[] = ['value' => $rule->getRuleId(), 'label' => __($rule->getName())];
        }

        return $options;
    }
}