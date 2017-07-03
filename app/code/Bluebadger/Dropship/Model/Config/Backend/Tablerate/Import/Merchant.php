<?php

namespace Bluebadger\Dropship\Model\Config\Backend\Tablerate\Import;

/**
 * Class Merchant
 * @package Bluebadger\Dropship\Model\Config\Backend
 */
class Merchant extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\MerchantFactory
     */
    protected $merchantFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * Merchant constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\MerchantFactory $merchantFactory
     * @param w $
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\MerchantFactory $merchantFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->merchantFactory = $merchantFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant $merchant */
        $merchant = $this->merchantFactory->create();

        try {
            $merchant->uploadAndImport($this);
        } catch (\Exception $e) {
            $message = 'Error while importing merchants: ' . $e->getMessage();
            $this->_logger->critical($message);
            $this->messageManager->addErrorMessage($message);
        }

        return parent::afterSave();
    }
}
