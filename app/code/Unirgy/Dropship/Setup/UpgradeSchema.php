<?php

namespace Unirgy\Dropship\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
  
        if (version_compare($context->getVersion(), '3.1.37', '<')) {
            $connection->addColumn(
                $installer->getTable('udropship_vendor'),
                'hide_vendor_name',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 2,
                    'nullable' => true,
                    'default'  => 0,
                    'comment'  => 'Hide Vendor Name'
                ]);
        }
        if (version_compare($context->getVersion(), '3.1.38', '<')) {
            $connection->addColumn(
                $installer->getTable('udropship_vendor'),
                'vendor_code',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => NULL,
                    'comment'  => 'Vendor Code'
                ]);
        }

        $installer->endSetup();
    }
}