<?php
/**
 * Awe Menu is quick, easy to setup and WYSIWYG menu management system
 *
 * Awe Menu by Kahanit(https://www.kahanit.com) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.1.0
 */

namespace Kahanit\AweMenu\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $table = $connection
            ->newTable($installer->getTable('awemenu'))
            ->addColumn('id', Table::TYPE_BIGINT, 20, ['nullable' => false, 'unsigned' => true, 'primary' => true, 'identity' => true])
            ->addColumn('title', Table::TYPE_TEXT, null, ['nullable' => false, 'default' => null])
            ->addColumn('shop', Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 0, 'unsigned' => true])
            ->addColumn('author', Table::TYPE_BIGINT, 20, ['nullable' => false, 'default' => 0, 'unsigned' => true])
            ->addColumn('menu', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => false, 'default' => null])
            ->addColumn('theme', Table::TYPE_TEXT, Table::MAX_TEXT_SIZE, ['nullable' => false, 'default' => null])
            ->addColumn('edit', Table::TYPE_SMALLINT, 5, ['nullable' => false, 'default' => 0, 'unsigned' => true])
            ->addColumn('live', Table::TYPE_SMALLINT, 5, ['nullable' => false, 'default' => 0, 'unsigned' => true])
            ->addColumn('deleted', Table::TYPE_SMALLINT, 5, ['nullable' => false, 'default' => 0, 'unsigned' => true])
            ->addColumn('date', Table::TYPE_INTEGER, 10, ['nullable' => false, 'default' => 0, 'unsigned' => true]);

        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}
