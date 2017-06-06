<?php

namespace Unirgy\DropshipVendorPromotions\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $salesRuleTable = $installer->getTable('salesrule');
        $connection->addColumn($salesRuleTable, 'udropship_vendor', ['TYPE' => Table::TYPE_INTEGER, 'unsigned'=>true, 'nullable' => true, 'COMMENT' => 'udropship_vendor']);

        $setup->endSetup();
    }
}