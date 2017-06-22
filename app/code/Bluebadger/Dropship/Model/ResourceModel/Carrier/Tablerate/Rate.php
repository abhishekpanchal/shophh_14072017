<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate;

class Rate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('bluebadger_dropship_tablerate_rate', 'rate_id');
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

        return $connection->fetchOne($select, $bind);
    }
}