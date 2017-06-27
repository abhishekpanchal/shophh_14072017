<?php

namespace Bluebadger\Dropship\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Bluebadger\Dropship\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    )
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bluebadger_dropship_tablerate_merchant'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_dropship_tablerate_merchant')
        )->addColumn(
            'merchant_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
            'Primary key'
        )->addColumn(
            'name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'Name'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'unsigned' => true],
            'Vendor ID'
        )->addColumn(
            'carrier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Carrier'
        )->addColumn(
            'origin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Origin'
        )->setComment(
            'Merchant list'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bluebadger_dropship_tablerate_zone
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_dropship_tablerate_zone')
        )->addColumn(
            'zone_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Primary key'
        )->addColumn(
            'area_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            3,
            ['nullable' => false, 'default' => '0'],
            'Area Code'
        )->addColumn(
            'carrier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Carrier Code'
        )->addColumn(
            'origin',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Origin'
        )->addColumn(
            'zone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            ['nullable' => false, 'default' => '0'],
            'Zone'
        )->setComment(
            'Zones'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bluebadger_dropship_tablerate_rate
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_dropship_tablerate_rate')
        )->addColumn(
            'rate_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Primary key'
        )->addColumn(
            'weight',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['nullable' => false, 'unsigned' => true, 'default' => '0'],
            'Lb'
        )->addColumn(
            'carrier',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Carrier'
        )->addColumn(
            'zone',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            5,
            ['nullable' => false, 'default' => '0'],
            'Zone'
        )->addColumn(
            'rate',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0'],
            'Rate'
        )->setComment(
            'Rate'
        );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'bluebadger_dropship_tablerate_quote_item'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_dropship_tablerate_quote_item')
        )->addColumn(
            'item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Primary key'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'default' => '0'],
            'Quote ID'
        )->addColumn(
            'quote_item_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'default' => '0'],
            'Quote item ID'
        )->addColumn(
            'shipping_cost',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0'],
            'Cost'
        )->addColumn(
            'vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['nullable' => false, 'default' => '0'],
            'Vendor ID'
        )->addColumn(
            'ship_time_low',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            2,
            ['nullable' => false, 'default' => '0'],
            'Lowest shipping time'
        )->addColumn(
            'ship_time_high',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            2,
            ['nullable' => false, 'default' => '0'],
            'Highest shipping time'
        )->addColumn(
            'ship_time_unit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false],
            'Ship time unit'
        )->addColumn(
            'weight',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0'],
            'Weight'
        )->addColumn(
            'call_for_quote',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            1,
            ['nullable' => false, 'default' => '0'],
            'Call for quote'
        )->setComment(
            'Quote Item'
        );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();

    }
}