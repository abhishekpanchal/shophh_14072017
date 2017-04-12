<?php

namespace Hhmedia\Tags\Setup;

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
  
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection->addColumn(
                $installer->getTable('hhmedia_tags'),
                'link_text',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link Text'
                ]);
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $connection->addColumn(
                $installer->getTable('hhmedia_tags'),
                'link_url',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '255',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link URL'
                ]);
        }
        $installer->endSetup();
    }
}