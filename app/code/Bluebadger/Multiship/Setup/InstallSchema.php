<?php

namespace Bluebadger\Multiship\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Bluebadger\Multiship\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @inheritdoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'bluebadger_multiship_rate'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_multiship_rate')
        )->addColumn(
            'pk',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Primary key'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Website Id'
        )->addColumn(
            'sku',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            64,
            ['nullable' => false, 'default' => '0'],
            'Product SKU'
        )->addColumn(
            'vendor',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false, 'default' => '0'],
            'Vendor'
        )->addColumn(
            'postcode',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => '*'],
            'Postcode/ZIP'
        )->addColumn(
            'region_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Province/State'
        )->addColumn(
            'country_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            4,
            ['nullable' => false, 'default' => '0'],
            'Destination Country ISO/2 or ISO/3 code'
        )->addColumn(
            'carrier_code',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            ['nullable' => false],
            'Carrier Code'
        )->addColumn(
            'cost',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '12,4',
            ['nullable' => false, 'default' => '0.0000'],
            'Cost per item'
        )->addColumn(
            'ship_time_unit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            20,
            ['nullable' => false, 'default' => 'days'],
            'Unit of time for shipping'
        )->addColumn(
            'ship_time_value',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            20,
            ['nullable' => false, 'default' => '0'],
            'Shipping time value'
        )->addIndex(
            $installer->getIdxName(
                'bluebadger_multiship_rate',
                ['website_id', 'sku', 'vendor', 'country_id', 'region_id', 'postcode'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['website_id', 'sku', 'vendor', 'country_id', 'region_id', 'postcode'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Multiship Rates'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}