<?php

namespace Unirgy\DropshipVendorTax\Setup;

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

        $vendorTable = $installer->getTable('udropship_vendor');
        $connection->addColumn($vendorTable, 'vendor_tax_class', ['TYPE' => Table::TYPE_TEXT, 'LENGTH' => 255, 'nullable' => true, 'COMMENT' => 'vendor_tax_class']);

        $taxCalcTable = $installer->getTable('tax_calculation');
        $connection->addColumn($taxCalcTable, 'vendor_tax_class_id', ['TYPE' => Table::TYPE_SMALLINT, 'nullable' => false, 'COMMENT' => 'vendor_tax_class']);

        $taxClassTable = $installer->getTable('tax_class');
        $connection->addForeignKey(
            $installer->getFkName($taxCalcTable, 'vendor_tax_class_id', $taxClassTable, 'class_id'),
            $taxCalcTable, 'vendor_tax_class_id', $taxClassTable, 'class_id'
        );

        $setup->endSetup();
    }
}