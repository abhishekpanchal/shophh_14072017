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

// inheritance
use Magento\Framework\Setup\UpgradeSchemaInterface;
// other
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            // alter table to make menu and theme datatype longtext
            $connection->changeColumn(
                $installer->getTable('awemenu'),
                'menu',
                'menu',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => Table::MAX_TEXT_SIZE,
                    'nullable' => false,
                    'default'  => null,
                    'comment'  => 'Menu'
                ]);
            $connection->changeColumn(
                $installer->getTable('awemenu'),
                'theme',
                'theme',
                [
                    'type'     => Table::TYPE_TEXT,
                    'length'   => Table::MAX_TEXT_SIZE,
                    'nullable' => false,
                    'default'  => null,
                    'comment'  => 'Theme'
                ]);
        }

        $installer->endSetup();
    }
}
