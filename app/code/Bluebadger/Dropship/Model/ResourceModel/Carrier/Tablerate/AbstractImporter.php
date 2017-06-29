<?php
namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractImporter
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate
 */
abstract class AbstractImporter
{
    /**
     * @var \Bluebadger\Dropship\Helper\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var bool
     */
    private $isFirstRowHeaders;

    /**
     * @var array
     */
    private $importedData;

    /**
     * @var array $errors
     */
    private $errors;


    /**
     * AbstractImporter constructor.
     * @param \Bluebadger\Dropship\Helper\Config $config
     * @param \Magento\Framework\File\Csv $csvProcessor
     */
    public function __construct(
        \Bluebadger\Dropship\Helper\Config $config,
        \Magento\Framework\File\Csv $csvProcessor
    )
    {
        $this->config = $config;
        $this->csvProcessor = $csvProcessor;
    }

    /**
     * Set file path.
     * @param string $filePath
     */
    public function setFilePath(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param bool $isFirstRowHeaders
     */
    public function setIsFirstRowHeaders(bool $isFirstRowHeaders)
    {
        $this->isFirstRowHeaders = $isFirstRowHeaders;
    }


    /**
     * Import data from the CSV file into an array.
     */
    public function import()
    {
        if (!isset($this->filePath)) {
            throw new LocalizedException(__('File path is not set.'));
        }

        $csvData = $this->csvProcessor->getData($this->filePath);
        $this->importedData = [];
        $fields = [];

        /**
         * @var int $rowIndex
         * @var array $csvDatum
         */
        foreach ($csvData as $rowIndex => $csvDatum) {
            if ($rowIndex == 0 && $this->isFirstRowHeaders) {
                $fields = $csvDatum;
                continue;
            }

            try {
                $data = $this->processRow($csvDatum, $fields);
                if ($this->isMulti($data)) {
                    $this->importedData = array_merge($this->importedData, $data);
                } else {
                    $this->importedData[] = $data;
                }
            } catch (\Exception $e) {
                $this->errors[] = 'Error at line ' . $rowIndex . ': ' . $e->getMessage();
            }
        }
    }

    /**
     * Return error flag.
     * @return bool
     */
    public function hasErrors()
    {
        return (bool)!empty($this->errors);
    }

    /**
     * Return an array of errors.
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Return imported data.
     * @return array
     */
    public function getImportedData()
    {
        return $this->importedData;
    }

    /**
     * @param array $row
     */
    abstract function processRow(array $row, array $fields);

    private function isMulti($array)
    {
        return (count($array) != count($array, 1));
    }
}