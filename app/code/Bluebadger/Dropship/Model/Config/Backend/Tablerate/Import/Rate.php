<?php

namespace Bluebadger\Dropship\Model\Config\Backend\Tablerate\Import;

/**
 * Class Rate
 * @package Bluebadger\Dropship\Model\Config\Backend\Tablerate\Import
 */
class Rate extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory
     */
    protected $rateFactory;

    /**
     * Rate constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory $rateFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\RateFactory $rateFactory,
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
        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate $rate */
        $rate = $this->rateFactory->create();

        try {
            $rate->uploadAndImport($this);
        } catch (\Exception $e) {
            $this->_logger->critical('Error while importing rates: ' . $e->getMessage());
        }

        return parent::afterSave();
    }
}
