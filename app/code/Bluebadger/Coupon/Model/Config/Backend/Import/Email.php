<?php

namespace Bluebadger\Coupon\Model\Config\Backend\Import;
use Magento\Framework\Filesystem;

/**
 * Class Email
 * @package Bluebadger\Coupon\Model\Config\Backend\Import
 */
class Email extends \Magento\Config\Model\Config\Backend\File
{
    const KEY_TABLENAME = 'bluebadger_coupon_email';
    /**
     * @var  \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData
     * @param Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Config\Model\Config\Backend\File\RequestData\RequestDataInterface $requestData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $uploaderFactory, $requestData, $filesystem, $resource, $resourceCollection, $data);
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        $fileData = $this->getFileData();

        if ($fileData) {
            $uploadDir = $this->_filesystem->getDirectoryRead('pub')->getAbsolutePath();
            $filename = $this->getValue();

            try {
                $csvData = $this->csvProcessor->getData($uploadDir . 'email' . DIRECTORY_SEPARATOR . $filename);
                $connection = $this->_resource->getConnection();
                $connection->delete($this->getTableName());
                $connection->insertArray(
                    $this->getTableName(),
                    ['email'],
                    $csvData
                );
            } catch (\Exception $e) {
                throw $e;
            }
        }

        return parent::afterSave();
    }

    /**
     * @return string[]
     */
    public function getAllowedExtensions() {
        return ['csv'];
    }

    /**
     * Get table name
     * @return string
     */
    private function getTableName()
    {
        $connection = $this->_resource->getConnection();
        return $connection->getTableName(self::KEY_TABLENAME);
    }
}