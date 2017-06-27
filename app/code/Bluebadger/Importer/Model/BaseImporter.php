<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:14
 */

namespace Bluebadger\Importer\Model;

use Bluebadger\Importer\Exception\CsvFileNotSetException;
use Bluebadger\Importer\Helper\Config;
use Magento\Framework\File\Csv;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class BaseImporter
 * @package Bluebadger\Importer\Model
 */
class BaseImporter implements \Bluebadger\Importer\Api\Data\ImporterInterface
{
    const CSV_DELIMITER = '|';

    /**
     * @var string
     */
    protected $csvFilePath;

    /**
     * @var Csv
     */
    protected $fileCsv;

    /**
     * @var Config
     */
    protected $configHelper;

    public function __construct(
        Csv $fileCsv,
        Config $configHelper
    )
    {
        $this->fileCsv = $fileCsv;
        $this->configHelper = $configHelper;
        $this->fileCsv->setDelimiter(self::CSV_DELIMITER);
    }

    /**
     * {@inheritdoc}
     */
    public function setCsvFilePath($csvFilename)
    {
        $this->csvFilePath = $csvFilename;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return $this->getEntityName();
    }

    /**
     * {@inheritdoc}
     */
    public function process()
    {
        if (!isset($this->csvFilePath)) {
            throw new CsvFileNotSetException(__('CSV file is not set'));
        } else if (!file_exists($this->csvFilePath)) {
            throw new FileNotFoundException($this->csvFilePath);
        } else {
            $rows = $this->fileCsv->getData($this->csvFilePath);
            $headers = [];

            foreach ($rows as $key => $row) {
                if ($key == 0) {
                    $headers = $row;
                } else {
                    $this->handle(array_combine($headers, $row));
                }
            }
        }

        $this->processAfter();
    }

    /**
     * {@inheritdoc}
     */
    public function processUpdate()
    {
        if (!isset($this->csvFilePath)) {
            throw new CsvFileNotSetException(__('CSV file is not set'));
        } else if (!file_exists($this->csvFilePath)) {
            throw new FileNotFoundException($this->csvFilePath);
        } else {
            $rows = $this->fileCsv->getData($this->csvFilePath);
            $headers = [];

            foreach ($rows as $key => $row) {
                if ($key == 0) {
                    $headers = $row;
                } else {
                    $this->handleUpdate(array_combine($headers, $row));
                }
            }
        }

        $this->processAfter();
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $row)
    {
        $this->handle($row);
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdate(array $row)
    {
        $this->handleUpdate($row);
    }

    /**
     * {@inheritdoc}
     */
    public function log(string $logLevel, $message)
    {
        $this->configHelper->log($logLevel, $message);
    }

    /**
     * Stuff to do after.
     */
    public function processAfter()
    {
    }
}