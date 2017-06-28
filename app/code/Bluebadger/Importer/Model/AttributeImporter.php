<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:11
 */

namespace Bluebadger\Importer\Model;
use Bluebadger\Importer\Helper\Config;
use Bluebadger\Importer\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\File\Csv;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LogLevel;

/**
 * Class AttributeImporter
 * @package Bluebadger\Importer\Model
 */
class AttributeImporter extends BaseImporter
{
    const ENTITY_NAME = 'Attribute';
    const OPTION_SEPARATOR_CHAR = ';';
    const CATALOG_PRODUCT_ENTITY_TYPE_ID = 4;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $soureModelMap = [
        'eav/entity_attribute_source_table' => 'Magento\Eav\Model\Entity\Attribute\Source\Table',
        'eav/entity_attribute_backend_array' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
        'eav/entity_attribute_source_boolean' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
        'Magento\Catalog\Model\Product\Attribute\Backend\Weight' => 'Magento\Catalog\Model\Product\Attribute\Backend\Weight',
        'Bluebadger\Supplier\Model\ResourceModel\Retailer\Source\Retailer' => 'Bluebadger\Supplier\Model\ResourceModel\Retailer\Source\Retailer'
    ];

    protected $backendModelMap = [
        'eav/entity_attribute_backend_array' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend'
    ];

    protected $frontendInputRendererMap = [
        'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight'
    ];

    protected $backendRendererMap = [];

    protected $colorOptions = ['Red', 'Pink', 'Orange', 'Yellow', 'Purple', 'Green', 'Blue', 'Brown', 'White', 'Grey', 'Black'];

    protected $widthOptions = ['Under 20in', '20in.-23.9in', '24in-35.9in', '36-41.9in', '42-47.9in', '48-59.9in', '60-71.9in', '72in. and over'];

    protected $finishOptions = ['White','Black', 'Grey', 'Brown', 'Colors', 'Chrome', 'Brushed nickel', 'Wood/brown', 'Other'];

    protected $faucetInstallationOptions = ['Deck Mount', 'Freestanding faucet', 'Wall mount faucet'];

    protected $installationOptions = ['horizontal', 'vertical'];

    /**
     * AttributeImporter constructor.
     * @param Csv $fileCsv
     * @param Config $configHelper
     * @param Data $dataHelper
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Csv $fileCsv,
        Config $configHelper,
        Data $dataHelper,
        EavSetupFactory $eavSetupFactory,
        AttributeRepositoryInterface $attributeRepository,
        StoreManagerInterface $storeManager
    )
    {
        $this->dataHelper = $dataHelper;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->storeManager = $storeManager;
        parent::__construct($fileCsv, $configHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityName()
    {
        return self::ENTITY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $row)
    {
        $attributeCode = $row['attribute_code'];

        try {
            echo PHP_EOL;
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            if ($attribute) {
                if($attributeCode == 'weight') {
                    $attribute->setDefaultFrontendLabel('Product Weight (lb.)');
                    $attribute->setUsedInProductListing(1);
                    $attribute->setIsVisibleInFront(1);
                    $this->attributeRepository->save($attribute);
                } else if($attributeCode == 'color') {
                    $attribute->setDefaultFrontendLabel('Color');
                    $attribute->setUsedInProductListing(1);
                    $attribute->setIsVisibleInFront(1);
//                    $attribute->setOptions(['Red', 'Pink', 'Orange', 'Yellow', 'Purple', 'Green', 'Blue', 'Brown', 'White', 'Grey', 'Black']);
                    $this->attributeRepository->save($attribute);
                    foreach($this->colorOptions as $colorOption) {
                        $optionId = $this->dataHelper->createOrGetId($attributeCode, $colorOption);
                        $message = __METHOD__ . ': ' . __LINE__ . ': Added attribute ' . $attributeCode . ' option ' . $colorOption . ' as ' . $optionId;
                        echo $message . PHP_EOL;
                        $this->log(LogLevel::ERROR, $message);
                    }
                } else {
                   $errorMessage = __METHOD__ . ': ' . __LINE__ . ': The attribute ' . $attributeCode . ' already exists... Skipping';
                    echo $errorMessage . PHP_EOL;
                   $this->log(LogLevel::ERROR, $errorMessage);
                }
            } else {
                $this->createAttribute($row, $attributeCode);
            }
        } catch (NoSuchEntityException $e) {
            $this->createAttribute($row, $attributeCode);
        } catch (\Exception $e) {
            $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot create attribute ' . $attributeCode . ': ' . $e->getMessage();
            echo $errorMessage . PHP_EOL;
            $this->log(LogLevel::ERROR, $errorMessage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdate(array $row)
    {
        $attributeCode = $row['attribute_code'];
        $toBeDeleted = $row['to_be_deleted'];
        $message = __METHOD__ . ': ' . __LINE__ . ': attribute code: ' . $attributeCode . ' and to be deleted: ' . $toBeDeleted;
        $this->log(LogLevel::ERROR, $message);
        echo $message . PHP_EOL;

        try {
            echo PHP_EOL;
            $attribute = $this->attributeRepository->get('catalog_product', $attributeCode);
            if ($attribute) {
                if($toBeDeleted == 'yes') {
                    try {
                        $message = __METHOD__ . ': ' . __LINE__ . ': attribute code: ' . $attributeCode . ' will be deleted: ' . $toBeDeleted;
                        $this->log(LogLevel::ERROR, $message);
                        echo $message . PHP_EOL;
                        $this->attributeRepository->delete($attribute);
                    } catch(\Exception $e) {
                        $errorMessage = __METHOD__ . ': ' . __LINE__ . ': The attribute ' . $attributeCode . ' cannot be deleted: ' . $e->getMessage();
                        $this->log(LogLevel::ERROR, $errorMessage);
                        echo $errorMessage . PHP_EOL;
                    }
                } else {
                    if($attributeCode == 'width') {
                        $attribute->setDefaultFrontendLabel('Width');
                        $attribute->setUsedInProductListing(1);
                        $attribute->setIsVisibleInFront(1);
                        $this->attributeRepository->save($attribute);
                        foreach($this->widthOptions as $widthOption) {
                            $optionId = $this->dataHelper->createOrGetId($attributeCode, $widthOption);
                            $message = 'Added attribute ' . $attributeCode . ' option ' . $widthOption . ' as ' . $optionId;
                            echo $message . PHP_EOL;
                            $this->log(LogLevel::ERROR, $message);
                        }
                    } else if($attributeCode == 'finish_filter') {
                        $attribute->setDefaultFrontendLabel('Finish');
                        $attribute->setUsedInProductListing(1);
                        $attribute->setIsVisibleInFront(1);
                        $this->attributeRepository->save($attribute);
                        foreach($this->finishOptions as $finishOption) {
                            $optionId = $this->dataHelper->createOrGetId($attributeCode, $finishOption);
                            $message = 'Added attribute ' . $attributeCode . ' option ' . $finishOption . ' as ' . $optionId;
                            echo $message . PHP_EOL;
                            $this->log(LogLevel::ERROR, $message);
                        }
                    } else if($attributeCode == 'faucet_installation') {
                        $attribute->setDefaultFrontendLabel('Faucet installation type');
                        $attribute->setUsedInProductListing(1);
                        $attribute->setIsVisibleInFront(1);
                        $this->attributeRepository->save($attribute);
                        foreach($this->faucetInstallationOptions as $faucetInstallationOption) {
                            $optionId = $this->dataHelper->createOrGetId($attributeCode, $faucetInstallationOption);
                            $message = 'Added attribute ' . $attributeCode . ' option ' . $faucetInstallationOption . ' as ' . $optionId;
                            echo $message . PHP_EOL;
                            $this->log(LogLevel::ERROR, $message);
                        }
                    } else if($attributeCode == 'installation_horiz_vert') {
                        $attribute->setDefaultFrontendLabel('Installation horiz/vert');
                        $attribute->setUsedInProductListing(1);
                        $attribute->setIsVisibleInFront(1);
                        $this->attributeRepository->save($attribute);
                        foreach($this->installationOptions as $installationOption) {
                            $optionId = $this->dataHelper->createOrGetId($attributeCode, $installationOption);
                            $message = 'Added attribute ' . $attributeCode . ' option ' . $installationOption . ' as ' . $optionId;
                            echo $message . PHP_EOL;
                            $this->log(LogLevel::ERROR, $message);
                        }
                    } else {
                        $errorMessage = __METHOD__ . ': ' . __LINE__ . ': The attribute ' . $attributeCode . ' already exists... Skipping ...';
                        $this->log(LogLevel::ERROR, $errorMessage);
                        echo $errorMessage . PHP_EOL;
                    }
                }
            } else {
                if($toBeDeleted == 'no') {
                    $this->createAttribute($row, $attributeCode);
                }
            }
        } catch (NoSuchEntityException $e) {
            $this->createAttribute($row, $attributeCode);
        } catch (\Exception $e) {
            $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot create attribute ' . $attributeCode . ': ' . $e->getMessage();
            $this->log(LogLevel::ERROR, $errorMessage);
            echo $errorMessage . PHP_EOL;
        }
    }

    /**
     * Create an attribute by code.
     *
     * @param $row
     * @param $attributeCode
     */
    private function createAttribute($row, $attributeCode)
    {
        $message = __METHOD__ . ': ' . __LINE__ . ': Creating attribute ' . $attributeCode;
        echo $message . PHP_EOL;
        $this->log(LogLevel::ERROR, $message);

        if ($row['entity_type_id'] == self::CATALOG_PRODUCT_ENTITY_TYPE_ID) {
            /* Map backend model */
            if ($row['backend_model']) {
                if (isset($this->soureModelMap[$row['backend_model']])) {
                    $row['backend_model'] = $this->soureModelMap[$row['backend_model']];
                } else {
                    $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot map backend model ' . $row['backend_model'] . ' for attribute ' . $attributeCode . '. Skipping...';
                    $this->log(LogLevel::ERROR, $errorMessage);
                    echo $errorMessage . PHP_EOL;
                    return;
                }
            }

            /* Map source model */
            if ($row['source_model']) {
                if (isset($this->soureModelMap[$row['source_model']])) {
                    $row['source_model'] = $this->soureModelMap[$row['source_model']];
                } else {
                    $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot map source model ' . $row['source_model'] . ' for attribute ' . $attributeCode . '. Skipping...';
                    $this->log(LogLevel::ERROR, $errorMessage);
                    echo $errorMessage . PHP_EOL;
                    return;
                }
            }

            /* Map backend renderer */
            if ($row['backend_renderer']) {
                if (isset($this->backendRendererMap[$row['backend_renderer']])) {
                    $row['backend_renderer'] = $this->backendRendererMap[$row['backend_renderer']];
                } else {
                    $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot map backend input renderer model ' . $row['backend_renderer'] . ' for attribute ' . $attributeCode . '. Skipping...';
                    $this->log(LogLevel::ERROR, $errorMessage);
                    echo $errorMessage . PHP_EOL;
                    return;
                }
            }

            /* Map frontend input renderer */
            if ($row['frontend_input_renderer']) {
                if (isset($this->frontendInputRendererMap[$row['frontend_input_renderer']])) {
                    $row['frontend_input_renderer'] = $this->frontendInputRendererMap[$row['frontend_input_renderer']];
                } else {
                    $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Cannot map frontend input renderer model ' . $row['frontend_input_renderer'] . ' for attribute ' . $attributeCode . '. Skipping...';
                    $this->log(LogLevel::ERROR, $errorMessage);
                    echo $errorMessage . PHP_EOL;
                    return;
                }
            }

            $eavSetup = $this->eavSetupFactory->create();

            try {
                $data = [
                    'type' => $row['backend_type'],
                    'backend' => $row['backend_model'],
                    'frontend' => $row['frontend_model'],
                    'label' => $row['frontend_label'],
                    'input' => $row['frontend_input'],
                    'class' => $row['frontend_class'],
                    'source' => $row['source_model'],
                    'global' => $row['is_global'],
                    'visible' => $row['is_visible'],
                    'required' => $row['is_required'],
                    'user_defined' => $row['is_user_defined'],
                    'default' => $row['default_value'],
                    'searchable' => $row['is_searchable'],
                    'filterable' => $row['is_filterable'],
                    'comparable' => $row['is_comparable'],
                    'visible_on_front' => $row['is_visible_on_front'],
                    'used_in_product_listing' => $row['used_in_product_listing'],
                    'unique' => $row['is_unique'],
                    'apply_to' => $row['apply_to'],
                    'note' => $row['note'],
                    'wysiwyg_enabled' => $row['is_wysiwyg_enabled'],
                    'used_for_promo_rules' => $row['is_used_for_promo_rules'],
                    'search_weight' => $row['search_weight'],
//                    'configurable' => $row['is_configurable']
                ];

                if (isset($row['_options'])) {
                    $data['option'] = ['values' => explode(';', $row['_options'])];
                }

                $eavSetup->addAttribute(Product::ENTITY, $attributeCode, $data);
                $message = __METHOD__ . ': ' . __LINE__ . ': Attribute ' . $attributeCode . ' created ....';
                echo $message . PHP_EOL;
                $this->log(LogLevel::ERROR, $message);
            } catch (\Exception $e) {
                $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Could not create attribute ' . $attributeCode . ': ' . $e->getMessage();
                $this->log(LogLevel::ERROR, $errorMessage);
                echo $errorMessage . PHP_EOL;
            }
        } else {
            $message = __METHOD__ . ': ' . __LINE__ . ': Attribute is not of type Catalog/Product. Skipping ...';
            echo $message . PHP_EOL;
            $this->log(LogLevel::ERROR, $message);
        }
    }
}