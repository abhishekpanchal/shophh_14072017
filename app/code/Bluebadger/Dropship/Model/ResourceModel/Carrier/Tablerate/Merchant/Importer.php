<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate;

use Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\AbstractImporter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\WebsiteInterface;

/**
 * Class Import
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Rate
 */
class Importer extends AbstractImporter
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
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    protected $countryInformationAcquirer;

    /**
     * @var WebsiteInterface
     */
    private $website;

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
     * @var array
     */
    private $regionMap;

    /**
     * @var array
     */
    private $carrierMap;

    /**
     * Import constructor.
     * @param \Bluebadger\Dropship\Helper\Config $config
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer
     */
    public function __construct(
        \Bluebadger\Dropship\Helper\Config $config,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Catalog\Model\Product $product,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer
        )
    {
        $this->config = $config;
        $this->csvProcessor = $csvProcessor;
        $this->product = $product;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * Set website.
     * @param WebsiteInterface $website
     */
    public function setWebsite(WebsiteInterface $website)
    {
        $this->website = $website;
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

        /**
         * @var int $rowIndex
         * @var array $csvDatum
         */
        foreach ($csvData as $rowIndex => $csvDatum) {
            if ($rowIndex == 0 && $this->isFirstRowHeaders) {
                continue;
            }

            try {
                $this->importedData[] = $this->processRow($csvDatum);
            } catch (\Exception $e) {
                echo $e->getMessage(); die;
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
     * Return column names.
     * @return array
     */
    public function getCsvFields()
    {
        return [
            Rate::FIELD_SKU,
            Rate::FIELD_VENDOR,
            Rate::FIELD_POSTCODE,
            Rate::FIELD_REGION,
            Rate::FIELD_COUNTRY,
            Rate::FIELD_CARRIER,
            Rate::FIELD_COST,
            Rate::FIELD_SHIP_TIME_UNIT,
            Rate::FIELD_SHIP_TIME_VALUE
        ];
    }

    /**
     * @param array $row
     */
    private function processRow(array $row)
    {
        $fields = [];

        /* Basic validation */
        foreach ($this->getCsvFields() as $index => $fieldName) {
            if (!isset($row[$index])) {
                throw new LocalizedException(__('Field ' . $fieldName . ' is missing'));
            }

            $fields[$fieldName] = trim($row[$index]);

            if (in_array($fieldName, ['country', 'region', 'postcode'])) {
                $fields[$fieldName] = strtoupper($fields[$fieldName]);
            }
        }

        /* Set website ID */
        if (!isset($this->website)) {
            throw new LocalizedException(__('Website ID is not set.'));
        }
        $fields[Rate::FIELD_WEBSITE_ID] = $this->website->getId();

        /* Validate country code */
        $countryCode = $fields[Rate::FIELD_COUNTRY];
        $allowedCountries = explode(',', $this->config->getSpecificCountry());

        if (!empty($allowedCountries) && !in_array($countryCode, $allowedCountries)) {
            throw new LocalizedException(__('Country code ' . $countryCode . ' is not allowed.'));
        }

        $regionMap = $this->getRegionMap($countryCode);

        /* Validate region */
        if (!isset($regionMap[$fields[Rate::FIELD_REGION]])) {
            throw new LocalizedException(__('Region code ' . $fields[Rate::FIELD_REGION] . ' does not exist'));
        }

        $fields[Rate::FIELD_REGION_ID] = $regionMap[$fields[Rate::FIELD_REGION]];

        /* Validate carrier */
        $allowedCarriers = explode(',', $this->config->getSpecificCarrier());

        if (!empty($allowedCarriers) && !in_array($fields[Rate::FIELD_CARRIER], $allowedCarriers)) {
            throw new LocalizedException(__('Carrier code ' . $fields[Rate::FIELD_CARRIER] . ' is not allowed'));
        }

        /* Validate cost */
        if ((float)$fields[Rate::FIELD_COST] < 0) {
            throw new LocalizedException(__('Cost ' . $fields[Rate::FIELD_COST] . ' is not a valid value'));
        }

        return $fields;
    }

    /**
     * @param string $countryCode
     * @return array
     */
    private function getRegionMap(string $countryCode)
    {
        if (!$this->regionMap) {
            $countryInfo = $this->countryInformationAcquirer->getCountryInfo($countryCode);
            $regions = $countryInfo->getAvailableRegions();

            if ($regions) {
                /** @var \Magento\Directory\Api\Data\RegionInformationInterface $region */
                foreach ($regions as $region) {
                    $this->regionMap[$region->getCode()] = $region->getId();
                }
            }
        }

        return $this->regionMap;
    }
}