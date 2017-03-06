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
        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'sharetwitter',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 500,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Share Copy(Twitter)'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'shareother',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => Table::MAX_TEXT_SIZE,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Share Copy(Other)'
                ]);
        }
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'bg_image',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Background Image'
                ]);
        }
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'display_home',
                [
                    'type'     => Table::TYPE_SMALLINT,
                    'length'   => 5,
                    'nullable' => true,
                    'default'  => 1,
                    'comment'  => 'Display on Home'
                ]);
        }

        if (version_compare($context->getVersion(), '2.0.6', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'color',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Shot Title Color'
                ]);
        }
		
		if (version_compare($context->getVersion(), '2.0.7', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'short_description',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description for Homepage'
                ]);
        }

        $installer->endSetup();
    }
}