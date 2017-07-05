<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 18:35
 */

namespace Bluebadger\Importer\Setup;

use Bluebadger\Importer\Helper\Config;
use Bluebadger\Importer\Model\AttributeImporter;
use Bluebadger\Importer\Model\AttributeSetsImporter;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LogLevel;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    const WAIT_TIME_SECONDS = 10;

    /**
     * @var AttributeSetsImporter
     */
    protected $attributeSetsImporter;

    /**
     * @var AttributeImporter
     */
    protected $attributeImporter;

    /**
     * @var Config
     */

    protected $configHelper;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;


    public function __construct(
        AttributeSetsImporter $attributeSetsImporter,
        AttributeImporter $attributeImporter,
        Config $configHelper,
        StoreFactory $storeFactory,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->attributeSetsImporter = $attributeSetsImporter;
        $this->attributeImporter = $attributeImporter;
        $this->configHelper = $configHelper;
        $this->storeFactory = $storeFactory;
        $this->storeManager       = $storeManager;
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $currentVersion = $context->getVersion();

        if (version_compare($currentVersion, '0.1.1') < 0) {
            //code to upgrade to 0.1.1
            $this->attributeImporter->setCsvFilePath($this->configHelper->getFilePathAttributesCsv());
            $this->attributeImporter->process();
            sleep(self::WAIT_TIME_SECONDS);
            $this->attributeSetsImporter->setCsvFilePath($this->configHelper->getFilePathAttributeSetsCsv());
            $this->attributeSetsImporter->process();
            sleep(self::WAIT_TIME_SECONDS);
            $this->attributeSetsImporter->process();
        }

        if (version_compare($currentVersion, '0.1.2') < 0) {
            /* Delete old attribute */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ships_from_warehouse_unit');

            /* Re-created attribute */
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'ships_from_warehouse_unit',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Ships From Warehouse Unit',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'eav/entity_attribute_source_table',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => true,
                    'required' => true,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => '',
                    'option' => ['values' => ['Day', 'Week']]
                ]
            );

            $eavSetup->addAttributeToGroup(\Magento\Catalog\Model\Product::ENTITY, 'House & Home', 'Shipping', 'ships_from_warehouse_unit',3);
        }

        $setup->endSetup();
    }

    /**
     * @param string $logLevel
     * @param $message
     */
    public function log(string $logLevel, $message)
    {
        $this->configHelper->log($logLevel, $message);
    }
}