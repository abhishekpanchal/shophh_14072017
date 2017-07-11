<?php

namespace Bluebadger\Coupon\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Bluebadger\Coupon\Setup
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
         * Create table 'bluebadger_coupon_email'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('bluebadger_coupon_email')
        )->addColumn(
            'email_id',
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
            'email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            ['nullable' => false],
            'email'
        )->setComment(
            'Valid email addresses'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}