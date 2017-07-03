<?php

namespace Bluebadger\Dropship\Model\Config\Backend\Tablerate\Import;

/**
 * Class Zone
 * @package Bluebadger\Dropship\Model\Config\Backend\Tablerate\Import
 */
class Zone extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\ZoneFactory
     */
    protected $zoneFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Zone constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\ZoneFactory $zoneFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\ZoneFactory $zoneFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->zoneFactory = $zoneFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Zone $zone */
        $zone = $this->zoneFactory->create();

        try {
            $zone->uploadAndImport($this);
        } catch (\Exception $e) {
            $message = 'Error while importing zones: ' . $e->getMessage();
            $this->_logger->critical($message);
            $this->messageManager->addErrorMessage($message);
        }

        return parent::afterSave();
    }
}
