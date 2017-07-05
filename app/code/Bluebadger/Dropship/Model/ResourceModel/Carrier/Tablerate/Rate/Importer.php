<?php
namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate;

use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\AbstractImporter;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Importer
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Rate
 */
class Importer extends AbstractImporter
{
    /**
     * @inheritdoc
     */
    public function processRow(array $row, array $fields)
    {
        $records = [];

        if (empty($fields)) {
            throw new LocalizedException(__('Carrier information is missing.'));
        }

        /* The carriers are store in the fields */
        foreach ($fields as $key => $field) {
            if ($key == 0 || empty($field)) {
                continue;
            }

            $chunks = explode('-', $field);

            if (!isset($chunks[0])) {
                throw new LocalizedException(__('Carrier is missing.'));
            }

            $carrier = $chunks[0];

            if (!isset($chunks[1])) {
                throw new LocalizedException(__('Zone is missing.'));
            }

            $zone = $chunks[1];

            $records[] = [
                Rate::FIELD_WEIGHT => trim($row[0]),
                Rate::FIELD_CARRIER => trim($carrier),
                Rate::FIELD_ZONE => trim($zone),
                Rate::FIELD_RATE => floatval(trim(str_replace('$', '', $row[$key])))
            ];
        }

        return $records;
    }
}