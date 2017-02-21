<?php

namespace Hhmedia\Formula\Setup;

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
                $installer->getTable('hhmedia_formula'),
                'content',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description'
                ]);
        }

        $installer->endSetup();
    }
}