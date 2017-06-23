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
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param float $shippingCost
     */
    public function updateItem(\Magento\Quote\Model\Quote\Item $item, float $shippingCost)
    {
        $data = [
            'quote_item_id' => $item->getId(),
            'merchant' => 'merchant',
            'carrier' => 'carrier',
            'zone' => 'zone',
            'rate' => 10.0,
            'shipping_cost' => $shippingCost
        ];

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);
    }
}