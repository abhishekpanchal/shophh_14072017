<?php

namespace Bluebadger\Multiship\Model\Config\Backend;

/**
 * Class Rate
 * @package Bluebadger\Multiship\Model\Config\Backend
 */
class Rate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bluebadger\Multiship\Model\ResourceModel\Carrier\RateFactory
     */
    protected $rateFactory;

    /**
     * Rate constructor.
     * TODO Display number of records successfully importer
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Bluebadger\Multiship\Model\ResourceModel\Carrier\RateFactory $rateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Bluebadger\Multiship\Model\ResourceModel\Carrier\RateFactory $rateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->rateFactory = $rateFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        /** @var \Bluebadger\Multiship\Model\ResourceModel\Carrier\Rate $rate */
        $rate = $this->rateFactory->create();

        try {
            $rate->uploadAndImport($this);
        } catch (\Exception $e) {
            $this->_logger->critical('Error while importing rates: ' . $e->getMessage());
        }

        return parent::afterSave();
    }
}
