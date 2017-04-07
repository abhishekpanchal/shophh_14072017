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
        if (version_compare($context->getVersion(), '2.0.8', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'sku_one',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'SKU ONE'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'sku_two',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'SKU Two'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'sku_three',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'SKU Three'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'sku_four',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'SKU Four'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'collection_link',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Collection Link'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'image_one',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Image 1'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'title_one',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Title 1'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'description_one',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description 1'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'link_one',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link 1'
                ]);
        }
        if (version_compare($context->getVersion(), '2.0.9', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'image_two',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Image 2'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'title_two',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Title 2'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'description_two',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description 2'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'link_two',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link 2'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'image_three',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Image 3'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'title_three',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Title 3'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'description_three',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description 3'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'link_three',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link 3'
                ]);  
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'image_four',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Image 4'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'title_four',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Title 4'
                ]);
        }
        if (version_compare($context->getVersion(), '2.0.10', '<')) {
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'description_four',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => '2M',
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Description 4'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'link_four',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Link 4'
                ]);
            $connection->addColumn(
                $installer->getTable('altima_lookbookslider_slide'),
                'collection_title',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'default'  => null,
                    'comment'  => 'Collection Title'
                ]);
        }

        $installer->endSetup();
    }
}