<?php


namespace Hhmedia\Formula\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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

		/**
		 * Creating table hhmedia_formula
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('hhmedia_formula')
		)->addColumn(
			'formula_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Formula Title'
		)->addColumn(
			'subtitle',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Subtitle'
		)->addColumn(
			'link',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			500,
			['nullable' => true,'default' => null],
			'Subtitle'
		)->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Custom Link'
		)->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Sort Order'
		)->addColumn(
			'created_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
			null,
			['nullable' => false],
			'Created At'
		)->addColumn(
			'published_at',
			\Magento\Framework\DB\Ddl\Table::TYPE_DATE,
			null,
			['nullable' => true,'default' => null],
			'World publish date'
		)->addIndex(
			$installer->getIdxName(
				'hhmedia_formula',
				['published_at'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
			),
			['published_at'],
			['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
		)->setComment(
			'Formula item'
		);
		$installer->getConnection()->createTable($table);

		/* Product table for Decorating Formulas */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('hhmedia_formula_product'))
            ->addColumn('formula_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true])
            ->addColumn('product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Magento Product Id')
            ->addForeignKey(
                $installer->getFkName(
                    'hhmedia_formula',
                    'formula_id',
                    'hhmedia_formula_product',
                    'formula_id'
                ),
                'formula_id',
                $installer->getTable('hhmedia_formula'),
                'formula_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'hhmedia_formula_product',
                    'formula_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Formula Products');

        $installer->getConnection()->createTable($table);

		$installer->endSetup();
	}
}