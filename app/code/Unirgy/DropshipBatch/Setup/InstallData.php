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

namespace Unirgy\DropshipBatch\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Catalog\Setup\CategorySetupFactory;
use Unirgy\Dropship\Setup\QuoteSetupFactory;
use Unirgy\Dropship\Setup\SalesSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    const MEDIUMTEXT_SIZE = 16777216;
    const TEXT_SIZE = 65536;
    protected $categorySetupFactory;
    protected $quoteSetupFactory;
    protected $salesSetupFactory;

    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    )
    {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Unirgy\Dropship\Setup\SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $setup = $salesSetup->getSetup();
        $conn = $setup->getConnection();
        $salesSetup->addAttribute('shipment', 'udropship_batch_status', ['type'=>Table::TYPE_TEXT, 'length'=>64]);
    }
}