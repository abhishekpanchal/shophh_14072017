<?php

namespace Altima\Lookbookslider\Setup;

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
  
        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $connection->changeColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'caption',
                'caption',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => Table::MAX_TEXT_SIZE,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Shot Description'
                ]);
        }
        $installer->endSetup();
    }
}