<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Filesystem;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate\ImportFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Rate
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate
 */
class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const FIELD_WEBSITE_ID = 'website_id';
    const FIELD_SKU = 'sku';
    const FIELD_VENDOR = 'vendor';
    const FIELD_POSTCODE = 'postcode';
    const FIELD_REGION = 'region';
    const FIELD_REGION_ID = 'region_id';
    const FIELD_COUNTRY = 'country';
    const FIELD_CARRIER = 'carrier';
    const FIELD_COST = 'cost';
    const FIELD_SHIP_TIME_UNIT = 'ship_time_unit';
    const FIELD_SHIP_TIME_VALUE = 'ship_time_value';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate\ImportFactory
     */
    protected $importFactory;

    /**
     * Rate constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Filesystem $filesystem
     * @param ImportFactory $importFactory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        ImportFactory $importFactory,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->importFactory = $importFactory;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bluebadger_dropship_rate', 'pk');
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['dropship']['fields']['import']['value'])) {
            return $this;
        }
        $filePath = $_FILES['groups']['tmp_name']['dropship']['fields']['import']['value'];
        $websiteId = $this->storeManager->getWebsite($object->getScopeId())->getId();

        $file = $this->getCsvFile($filePath);

        try {
            /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate\Import $importer */
            $importer = $this->importFactory->create();
            $importer->setWebsite($this->storeManager->getWebsite());
            $importer->setFilePath($filePath);
            $importer->setIsFirstRowHeaders(true);
            $importer->import();

            if ($importer->hasErrors()) {
                throw new LocalizedException(
                    'Something when wrong while importing rates: ' . implode(', ', $importer->getErrors())
                );
            }
            $this->importData($this->getFields(), $importer->getImportedData());
        } catch (\Exception $e) {
            throw new LocalizedException(__('Something went wrong while importing rates: ' . $e->getMessage()));
        } finally {
            $file->close();
        }

        return $this;
    }

    /**
     * @param string $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    private function getCsvFile($filePath)
    {
        $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($filePath);
        return $tmpDirectory->openFile($path);
    }
    
    private function getFields()
    {
        return [
            self::FIELD_SKU,
            self::FIELD_VENDOR,
            self::FIELD_POSTCODE,
            self::FIELD_REGION,
            self::FIELD_COUNTRY,
            self::FIELD_CARRIER,
            self::FIELD_COST,
            self::FIELD_SHIP_TIME_UNIT,
            self::FIELD_SHIP_TIME_VALUE,
            self::FIELD_WEBSITE_ID,
            self::FIELD_REGION_ID
        ];
    }
    
    /**
     * @param array $fields
     * @param array $values
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    private function importData(array $fields, array $values)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        try {
            if (count($fields) && count($values)) {
                $this->getConnection()->insertArray($this->getMainTable(), $fields, $values);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollback();
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing table rates.')
            );
        }
        $connection->commit();
    }
}