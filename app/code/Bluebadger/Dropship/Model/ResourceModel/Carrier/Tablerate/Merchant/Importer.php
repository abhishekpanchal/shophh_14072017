<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant;

use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\AbstractImporter;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Importer
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant
 */
class Importer extends AbstractImporter
{
    const FIELD_CSV_MERCHANT = 'Merchant';
    const FIELD_CSV_CARRIER = 'Carrier';
    const FIELD_CSV_ORIGIN = 'Origin';

    /**
     * @var \Unirgy\Dropship\Model\ResourceModel\Vendor\CollectionFactory
     */
    protected $vendorCollectionFactory;

    /**
     * Importer constructor.
     * @param \Bluebadger\Dropship\Helper\Config $config
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Unirgy\Dropship\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
     */
    public function __construct(
        \Bluebadger\Dropship\Helper\Config $config,
        \Magento\Framework\File\Csv $csvProcessor,
        \Unirgy\Dropship\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory
    )
    {
        parent::__construct($config, $csvProcessor);
        $this->vendorCollectionFactory = $vendorCollectionFactory;
    }

    /**
     * Return column names from the CSV file.
     * @return array
     */
    public function getCsvFields()
    {
        return [
            self::FIELD_CSV_MERCHANT,
            self::FIELD_CSV_CARRIER
        ];
    }

    /**
     * @param array $row
     */
    public function processRow(array $row, array $fields)
    {
        $processedRow = [];

        /* Basic validation */
        foreach ($this->getCsvFields() as $index => $fieldName) {
            if (!isset($row[$index])) {
                throw new LocalizedException(__('Field ' . $fieldName . ' is missing.'));
            }

            $value = trim($row[$index]);

            if (empty($value)) {
                throw new LocalizedException(__('Field ' . $fieldName . ' does not have a value'));
            }

            $row[$index] = $value;
        }

        /** @var \Unirgy\Dropship\Model\ResourceModel\Vendor\Collection $vendor */
        $vendor = $this->vendorCollectionFactory->create();
        $vendor->addFieldToFilter('vendor_name', $row[0]);

        if (!$vendor->getSize()) {
            throw new LocalizedException(__('Merchant does not exist: ' . $row[0] . '.'));
        }
        $processedRow[Merchant::FIELD_NAME] = $row[0];
        $processedRow[Merchant::FIELD_VENDOR_ID] = $vendor->getFirstItem()->getId();
        $processedRow[Merchant::FIELD_CARRIER] = $row[1];
        $processedRow[Merchant::FIELD_ORIGIN] = $row[2];

        return $processedRow;
    }
}