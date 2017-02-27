<?php


namespace Hhmedia\Editor\Setup;

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
		 * Creating table hhmedia_editor
		 */
		$table = $installer->getConnection()->newTable(
			$installer->getTable('hhmedia_editor')
		)->addColumn(
			'editor_id',
			\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
			null,
			['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
			'Entity Id'
		)->addColumn(
			'name',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true],
			'Name'
		)->addColumn(
			'job_title',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Job Title'
		)->addColumn(
			'content',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Description'
		)->addColumn(
			'bio',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Bio'
		)->addColumn(
			'quote',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			'2M',
			['nullable' => true,'default' => null],
			'Quote'
		)->addColumn(
			'image',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => true,'default' => null],
			'Editor image media path'
		)->addColumn(
			'signature',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			null,
			['nullable' => true,'default' => null],
			'Signature'
		)->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Status'
		)->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Sort Order'
		)->addColumn(
			'subtitle',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Sub Title'
		)->addColumn(
			'color',
			\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
			255,
			['nullable' => true,'default' => null],
			'Color'
		)->addColumn(
            'guest',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Guest Editor'
		)->addColumn(
            'past',
            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            null, 
            ['nullable' => true, 'default' => '1'],
            'Past'
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
				'hhmedia_editor',
				['published_at'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX
			),
			['published_at'],
			['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX]
		)->setComment(
			'Editor item'
		);
		$installer->getConnection()->createTable($table);

		/* Product table for Editor */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('hhmedia_editor_product'))
            ->addColumn('editor_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true])
            ->addColumn('product_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Magento Product Id')
            ->addForeignKey(
                $installer->getFkName(
                    'hhmedia_editor',
                    'editor_id',
                    'hhmedia_editor_product',
                    'editor_id'
                ),
                'editor_id',
                $installer->getTable('hhmedia_editor'),
                'editor_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'hhmedia_editor_product',
                    'editor_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('Editor Products');

        $installer->getConnection()->createTable($table);

		$installer->endSetup();
	}
}