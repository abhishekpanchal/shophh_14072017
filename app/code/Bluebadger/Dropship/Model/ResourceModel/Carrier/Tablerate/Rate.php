<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate;

use Bluebadger\Dropship\Logger\Logger;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate\Importer;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate\ImporterFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Filesystem;

/**
 * Class Rate
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate
 */
class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const FIELD_WEIGHT = 'weight';
    const FIELD_CARRIER = 'carrier';
    const FIELD_ZONE = 'zone';
    const FIELD_RATE = 'rate';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ImporterFactory
     */
    protected $importerFactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Merchant constructor.
     * @param Context $context
     * @param Filesystem $filesystem
     * @param ImporterFactory $importerFactory
     * @param Logger $logger
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        Filesystem $filesystem,
        ImporterFactory $importerFactory,
        Logger $logger,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->filesystem = $filesystem;
        $this->importerFactory = $importerFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('bluebadger_dropship_tablerate_rate', 'rate_id');
    }

    /**
     * Upload a import data.
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['dropshiptablerate']['fields']['rate']['value'])) {
            return $this;
        }
        $filePath = $_FILES['groups']['tmp_name']['dropshiptablerate']['fields']['rate']['value'];
        $file = $this->getCsvFile($filePath);

        try {
            /** @var Importer $importer */
            $importer = $this->importerFactory->create();
            $importer->setFilePath($filePath);
            $importer->setIsFirstRowHeaders(true);
            $importer->import();

            if ($importer->hasErrors()) {
                $message = 'Something when wrong while importing rates: ';
                $message .= implode(', ', $importer->getErrors());
                throw new LocalizedException(__($message));
            }
            $this->importData($this->getColumns(), $importer->getImportedData());
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Something went wrong while importing rates: ' . $e->getMessage())
            );
        } finally {
            $file->close();
        }

        return $this;
    }

    /**
     * Get the path to the CSV file.
     * @param string $filePath
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     */
    private function getCsvFile($filePath)
    {
        $tmpDirectory = $this->filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $path = $tmpDirectory->getRelativePath($filePath);
        return $tmpDirectory->openFile($path);
    }

    /**
     * Return a list of columns to insert.
     * @return array
     */
    private function getColumns()
    {
        return [
            self::FIELD_WEIGHT,
            self::FIELD_CARRIER,
            self::FIELD_ZONE,
            self::FIELD_RATE
        ];
    }

    /**
     * Import data.
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
                $this->getConnection()
                    ->insertArray($this->getMainTable(), $fields, $values);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollback();
            throw new LocalizedException(__('Unable to import rate data'), $e);
        } catch (\Exception $e) {
            $connection->rollback();
            $this->logger->critical($e);
            throw new LocalizedException(
                __('Something went wrong while importing rate information.')
            );
        }
        $connection->commit();
    }

    /**
     * Return a rate ID by product ID, vendor ID and zone identifier.
     *
     * @param float weight
     * @param int $vendorId
     * @param string $zoneIdentifier
     * @return string
     */
    public function getRateIdByWeightVendorZone(float $weight, int $vendorId, string $zoneCode)
    {
        $connection = $this->getConnection();

        /*
         * SELECT `bstr`
         * FROM `bluebadger_dropship_tablerate_merchant` AS `bdtm`
         * JOIN `bluebadger_dropship_tablerate_zone` AS `bdtz` ON `bdtm`.`carrier_location` = `bdtz`.`carrier_location`
         * JOIN `bluebadger_dropship_tablerate_rate` AS `bstr` ON `bstr`.`carrier_zone` = `bdtz`.`zone`
         * WHERE `bdtm`.`vendor_id` = {$vendorId}
         * AND `bdtz`.`zone` = {$zoneCode}
         * AND `bdtr`.`lb` <= {$weight}
         * LIMIT 0, 1
         */

        /*
        $select = $connection->select()
            ->from($this->getMainTable(), 'rate_id')
            ->joinLeft(
                ['zone' => '']
            )
            ->where('sku = :sku');
        $bind = [
            ':weight' => round($weight),
            ':vendor_id' => $vendorId,
            ':zone' => $zoneCode
        ];
       */

        //return $connection->fetchOne($select, $bind);
    }
}