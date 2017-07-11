<?php
namespace Bluebadger\Dropship\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Bluebadger\Dropship\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('bluebadger_dropship_tablerate_merchant'),
                    'is_wholesaler',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'length' => 1,
                        'default' => 0,
                        'comment' => 'Is wholesaler'
                    ]
                );

            $installer->getConnection()
                ->addColumn(
                    $installer->getTable('bluebadger_dropship_tablerate_quote_item'),
                    'is_free',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                        'length' => 1,
                        'default' => 0,
                        'comment' => 'Is free'
                    ]
                );
        }
        $installer->endSetup();
    }
}