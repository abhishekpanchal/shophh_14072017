<?php

namespace Hhmedia\Tags\Setup;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $eavSetup= $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->removeAttribute(ProductAttributeInterface::ENTITY_TYPE_CODE,'product_tags');
            $eavSetup->addAttribute(
                ProductAttributeInterface::ENTITY_TYPE_CODE,
                'product_tags',
                [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Product Tags', 
                    'input' => 'multiselect',
                    'class' => '',
                    'source' => 'Hhmedia\Tags\Model\Config\Source\Options',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => 1,
                    'required' => false,
                    'user_defined' => 1,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false
                ]
            );      
        }
    }
}