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
     * TODO Ship time attributes must be changed.
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param float $shippingCost
     */
    public function updateItem(\Magento\Quote\Model\Quote\Item $item, float $shippingCost)
    {
        /** TODO Randomly generated dates for testing purposes */
        $item->setData('ship_time_low', rand(1, 4));
        $item->setData('ship_time_high', rand(5, 10));
        $item->setData('ship_time_unit', rand(1, 2));

        $data = [
            'quote_id' => $item->getQuoteId(),
            'quote_item_id' => $item->getId(),
            'merchant' => 'merchant',
            'carrier' => 'carrier',
            'zone' => 'zone',
            'rate' => 10.0,
            'shipping_cost' => $shippingCost,
            'vendor_id' => $item->getData('udropship_vendor'),
            'ship_time_low' => $item->getData('ship_time_low'),
            'ship_time_high' => $item->getData('ship_time_high'),
            'ship_time_unit' => $item->getData('ship_time_unit')
        ];

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
    }
}