<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    \Unirgy\Dropship
 * @copyright  Copyright (c) 2015-2016 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    const MEDIUMTEXT_SIZE=16777216;
    const TEXT_SIZE=65536;
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        $quoteShippingRateTable = $installer->getTable('quote_shipping_rate');
        $connection->addColumn($quoteShippingRateTable, 'udropship_vendor', ['TYPE'=>Table::TYPE_INTEGER,'nullable' => true,'unsigned' => true,'COMMENT'=>'udropship_vendor']);

        $quoteTable = $installer->getTable('quote');
        $connection->addColumn($quoteTable, 'udropship_shipping_details', ['TYPE'=>Table::TYPE_TEXT,'nullable' => true,'LENGTH'=>self::TEXT_SIZE,'COMMENT'=>'udropship_shipping_details']);

        $installer->endSetup();
    }
}