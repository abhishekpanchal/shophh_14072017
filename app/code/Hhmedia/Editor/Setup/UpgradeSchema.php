<?php

namespace Hhmedia\Editor\Setup;

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
  
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection->addColumn(
                $installer->getTable('hhmedia_editor'),
                'display_home',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 5,
                    'nullable' => true,
                    'default'  => 1,
                    'comment'  => 'Display on Home'
                ]);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection->addColumn(
                $installer->getTable('hhmedia_editor'),
                'short_quote',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Short Quote for Home'
                ]);
        }

        $installer->endSetup();
    }
}