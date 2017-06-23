<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Zone;

use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\AbstractImporter;
use Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Zone;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Importer
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Zone
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
            if ($key == 0) {
                continue;
            }

            /* Loop through carriers and build array */
            $chunks = explode('-', $field);

            if (!isset($chunks[0])) {
                throw new LocalizedException(__('Carrier is missing'));
            }

            $carrier = $chunks[0];

            if (!isset($chunks[1])) {
                throw new LocalizedException(__('Origin is missing'));
            }

            $origin = $chunks[1];

            $records[] = [
                Zone::FIELD_AREA_CODE => trim($row[0]),
                Zone::FIELD_CARRIER => trim($carrier),
                Zone::FIELD_ORIGIN => trim($origin),
                Zone::FIELD_ZONE => trim($row[$key])
            ];
        }

        return $records;
    }
}