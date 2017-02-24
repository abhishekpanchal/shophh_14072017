<?php

namespace Unirgy\DropshipBatch\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema  implements InstallSchemaInterface
{
    const MEDIUMTEXT_SIZE = 16777216;
    const TEXT_SIZE = 65536;

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $vendorTable = $installer->getTable('udropship_vendor');
        $connection->addColumn($vendorTable, 'batch_export_orders_method', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>20,'nullable' => false,'COMMENT'=>'batch_export_orders_method']);
        $connection->addColumn($vendorTable, 'batch_export_orders_schedule', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>50,'nullable' => false,'COMMENT'=>'batch_export_orders_schedule']);
        $connection->addColumn($vendorTable, 'batch_import_orders_method', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>20,'nullable' => false,'COMMENT'=>'batch_import_orders_method']);
        $connection->addColumn($vendorTable, 'batch_import_orders_schedule', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>50,'nullable' => false,'COMMENT'=>'batch_import_orders_schedule']);
        $connection->addColumn($vendorTable, 'batch_import_inventory_method', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>20,'nullable' => false,'COMMENT'=>'batch_import_inventory_method']);
        $connection->addColumn($vendorTable, 'batch_import_inventory_schedule', ['TYPE'=>Table::TYPE_TEXT,'LENGTH'=>50,'nullable' => false,'COMMENT'=>'batch_import_inventory_schedule']);
        $connection->addColumn($vendorTable, 'batch_import_inventory_ts', ['TYPE'=>Table::TYPE_DATETIME,'COMMENT'=>'batch_import_inventory_ts']);
        $connection->addColumn($vendorTable, 'batch_import_orders_ts', ['TYPE'=>Table::TYPE_DATETIME,'COMMENT'=>'batch_import_orders_ts']);

        $batchTableName = $installer->getTable('udropship_batch');
        $table = $connection->newTable($batchTableName)
            ->addColumn('batch_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('vendor_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('batch_type', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('batch_status', Table::TYPE_TEXT, 20, ['nullable' => false])
            ->addColumn('adapter_type', Table::TYPE_TEXT, 100, ['nullable' => false])
            ->addColumn('use_custom_template', Table::TYPE_TEXT, 128, ['nullable' => false])
            ->addColumn('rows_text', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => false])
            ->addColumn('per_po_rows_text', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => false])
            ->addColumn('num_rows', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('use_wildcard', Table::TYPE_SMALLINT, null, ['nullable' => false])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('scheduled_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('comments', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addColumn('notes', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->setComment('Dropship Batch Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $batchDistTableName = $installer->getTable('udropship_batch_dist');
        $table = $connection->newTable($batchDistTableName)
            ->addColumn('dist_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('batch_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('location', Table::TYPE_TEXT, 255, ['nullable' => false])
            ->addColumn('dist_status', Table::TYPE_TEXT, 20, ['nullable' => false])
            ->addColumn('error_info', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addColumn('created_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('updated_at', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addIndex(
                $installer->getIdxName(
                    $batchDistTableName,
                    ['batch_id'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['batch_id'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addIndex(
                $installer->getIdxName(
                    $batchDistTableName,
                    ['dist_status'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['dist_status'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName($batchDistTableName, 'batch_id', $batchTableName, 'batch_id'),
                'batch_id',
                $batchTableName,
                'batch_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Dropship Batch Dist Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $batchRowTableName = $installer->getTable('udropship_batch_row');
        $table = $connection->newTable($batchRowTableName)
            ->addColumn('row_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('batch_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('order_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('po_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('stockpo_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => true])
            ->addColumn('item_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('track_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => true])
            ->addColumn('order_increment_id', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('po_increment_id', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('stockpo_increment_id', Table::TYPE_TEXT, 50, ['nullable' => true])
            ->addColumn('item_sku', Table::TYPE_TEXT, 50, ['nullable' => false])
            ->addColumn('tracking_id', Table::TYPE_TEXT, 50, ['nullable' => true])
            ->addColumn('has_error', Table::TYPE_SMALLINT, null, ['nullable' => false])
            ->addColumn('error_info', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addColumn('row_json', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addIndex(
                $installer->getIdxName(
                    $batchRowTableName,
                    ['batch_id'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['batch_id'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName($batchRowTableName, 'batch_id', $batchTableName, 'batch_id'),
                'batch_id',
                $batchTableName,
                'batch_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Dropship Batch Row Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $batchInvRowTableName = $installer->getTable('udropship_batch_invrow');
        $table = $connection->newTable($batchInvRowTableName)
            ->addColumn('row_id', Table::TYPE_INTEGER, 10, [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary'  => true
            ])
            ->addColumn('batch_id', Table::TYPE_INTEGER, 10, ['unsigned' => true,'nullable' => false])
            ->addColumn('product_id', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addColumn('sku', Table::TYPE_TEXT, 50, ['nullable' => true])
            ->addColumn('vendor_sku', Table::TYPE_TEXT, 50, ['nullable' => true])
            ->addColumn('vendor_cost', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('stock_qty', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('vendor_title', Table::TYPE_TEXT, 255, ['nullable' => true])
            ->addColumn('stock_qty_add', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('vendor_price', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('shipping_price', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('special_price', Table::TYPE_DECIMAL, [12,4], ['nullable' => true])
            ->addColumn('special_from_date', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('special_to_date', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('stock_status', Table::TYPE_SMALLINT, null, ['nullable' => true])
            ->addColumn('status', Table::TYPE_SMALLINT, null, ['nullable' => true])
            ->addColumn('state', Table::TYPE_TEXT, 32, ['nullable' => true])
            ->addColumn('avail_state', Table::TYPE_TEXT, 32, ['nullable' => true])
            ->addColumn('avail_date', Table::TYPE_DATETIME, null, ['nullable' => true])
            ->addColumn('has_error', Table::TYPE_SMALLINT, null, ['nullable' => true])
            ->addColumn('error_info', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addColumn('row_json', Table::TYPE_TEXT, self::TEXT_SIZE, ['nullable'=>true])
            ->addIndex(
                $installer->getIdxName(
                    $batchInvRowTableName,
                    ['batch_id'],
                    AdapterInterface::INDEX_TYPE_INDEX
                ),
                ['batch_id'],
                ['type' => AdapterInterface::INDEX_TYPE_INDEX]
            )
            ->addForeignKey(
                $installer->getFkName($batchInvRowTableName, 'batch_id', $batchTableName, 'batch_id'),
                'batch_id',
                $batchTableName,
                'batch_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Dropship Batch Row Table')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $connection->createTable($table);

        $installer->endSetup();
    }
}