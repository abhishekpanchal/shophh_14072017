<?php

namespace Hhmedia\Override\Setup;

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
	            $installer->getTable('review_detail'),
	            'reason',
	            [
	                'type'     => Table::TYPE_TEXT,
	                'length'   => 1000,
	                'nullable' => true,
	                'default'  => NULL,
	                'comment'  => 'Reason for Not Approved Review'
	            ]);
		}

		if (version_compare($context->getVersion(), '1.0.2', '<')) {
			$connection->addColumn(
	            $installer->getTable('sales_shipment'),
	            'custom_shipping_amount',
	            [
	                'type'     => Table::TYPE_DECIMAL,
	                'length'   => '12,4',
	                'nullable' => true,
	                'default'  => NULL,
	                'comment'  => 'Custom Shipping Amount'
	            ]);
		}

		$installer->endSetup();

	}
}