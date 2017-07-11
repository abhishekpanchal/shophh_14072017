<?php

namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote;

use Bluebadger\Dropship\Logger\Logger;
use Magento\Framework\Model\ResourceModel\Db\Context;


/**
 * Class Item
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote
 */
class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const FIELD_QUOTE_UD = 'quote_id';
    const FIELD_QUOTE_ITEM_ID = 'quote_item_id';
    const FIELD_MERCHANT = 'merchant';
    const FIELD_CARRIER = 'carrier';
    const FIELD_RATE = 'rate';
    const FIELD_SHIPPING_COST = 'shipping_cost';
    const KEY_PACKAGE_WEIGHT_LBS = 'package_weight_lbs';

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
        Logger $logger,
        $connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        $this->logger = $logger;
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bluebadger_dropship_tablerate_quote_item', 'item_id');
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param float $shippingCost
     * @param bool $callForQuote
     * @param bool $isFree
     */
    public function updateItem(\Magento\Quote\Model\Quote\Item $item, float $shippingCost, bool $callForQuote = false, bool $isFree = false)
    {
        $data = [
            'quote_id' => $item->getQuoteId(),
            'quote_item_id' => $item->getId(),
            'shipping_cost' => $shippingCost,
            'vendor_id' => $item->getData('udropship_vendor'),
            'ship_time_low' => $item->getData('ship_time_low'),
            'ship_time_high' => $item->getData('ship_time_high'),
            'ship_time_unit' => $item->getData('ship_time_unit'),
            'weight' => $item->getData(self::KEY_PACKAGE_WEIGHT_LBS),
            'call_for_quote' => $callForQuote,
            'is_free' => $isFree
        ];

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
    }

    /**
     * @param int $quoteId
     * @param int $vendorId
     */
    public function deleteByQuoteId(int $quoteId, int $vendorId)
    {
        if (!empty($quoteId) && !empty($vendorId)) {
            $sql = "DELETE FROM `" . $this->getMainTable() . "` WHERE `quote_id` = {$quoteId} AND `vendor_id` = {$vendorId}";
            $this->getConnection()->query($sql);
        }
    }
}