<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $table = $installer->getConnection()->newTable(
                        $installer->getTable('altima_lookbookslider_slider')
                )->addColumn(
                        'slider_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Slider ID'
                )->addColumn(
                        'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slider Title'
                )->addColumn(
                        'content_before', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['nullable' => true], 'Slider Content Before'
                )->addColumn(
                        'content_after', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', ['nullable' => true], 'Slider Content After'
                )->addColumn(
                        'position', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 128, ['nullable' => true], 'Slider Position'
                )->addColumn(
                        'effect', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slider Effect'
                )->addColumn(
                        'width', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Slider Width'
                )->addColumn(
                        'height', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'default' => 0], 'Slider Height'
                )->addColumn(
                        'showslidenames', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'Show slide names'
                )->addColumn(
                        'include_slides_js', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'include slides js'
                )->addColumn(
                        'navigation', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'Slider navigation'
                )->addColumn(
                        'navigation_hover', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'Slider navigation hover'
                )->addColumn(
                        'thumbnails', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'Slider thumbnails'
                )->addColumn(
                        'no_resample', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 1, ['nullable' => false, 'default' => 1], 'Slider no resample'
                )->addColumn(
                        'time', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 7000], 'Slider time pause'
                )->addColumn(
                        'trans_period', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 1500], 'Slider transition period'
                )->addColumn(
                        'creation_time', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, [], 'Slider Creation Time'
                )->addColumn(
                        'is_active', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Slider Active'
                )->setComment(
                'Altima Lookbookslider Slider Table'
        );
        $installer->getConnection()->createTable($table);


        /**
         * Create table 'altima_lookbookslider_slide'
         */
        $table = $installer->getConnection()->newTable(
                        $installer->getTable('altima_lookbookslider_slide')
                )->addColumn(
                        'slide_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'Slide ID'
                )->addColumn(
                        'slider_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false], 'Slider ID'
                )->addColumn(
                        'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slide Title'
                )->addColumn(
                        'image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slide Image path'
                )->addColumn(
                        'hotspots', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, '2M', [], 'Slide hotspots'
                )->addColumn(
                        'link', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slide Link'
                )->addColumn(
                        'caption', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => true], 'Slide Caption'
                )->addColumn(
                        'position', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false], 'Slide Position'
                )->addColumn(
                        'is_active', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Is Active'
                //)->addIndex(
                //        $installer->getIdxName('altima_lookbookslider_slide', ['identifier']), ['identifier']
                )->setComment(
                'Altima Lookbookslider Slide Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
                        $installer->getTable('altima_lookbookslider_slider_relatedcategory')
                )->addColumn(
                        'slider_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'primary' => false], 'Slider ID'
                )->addColumn(
                        'related_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'primary' => false], 'Related Category ID'
                )->addForeignKey(
                        $installer->getFkName('altima_lookbookslider_slider_relatedcategory', 'slider_id', 'altima_lookbookslider_slider', 'slider_id'), 'slider_id', $installer->getTable('altima_lookbookslider_slider'), 'slider_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                'Altima Lookbookslider Slider To Category Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
                        $installer->getTable('altima_lookbookslider_slider_relatedpage')
                )->addColumn(
                        'slider_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'primary' => false], 'Slider ID'
                )->addColumn(
                        'related_id', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, null, ['nullable' => false, 'primary' => false], 'Related Page ID'
                )->addForeignKey(
                        $installer->getFkName('altima_lookbookslider_slider_relatedpage', 'slider_id', 'altima_lookbookslider_slider', 'slider_id'), 'slider_id', $installer->getTable('altima_lookbookslider_slider'), 'slider_id', \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )->setComment(
                'Altima Lookbookslider Slider To Cms Page Linkage Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

}
