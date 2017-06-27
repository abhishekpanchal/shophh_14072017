<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:15
 */

namespace Bluebadger\Importer\Model;

use Bluebadger\Importer\Helper\Config;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\File\Csv;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Psr\Log\LogLevel;

class AttributeSetsImporter extends BaseImporter
{
    const CATALOG_ENTITY_TYPE = self::DEFAULT_ATTRIBUTE_SET_ID;
    const ENTITY_NAME = 'Attribute Sets';
    const FIELD_ATTRIBUTE_SET_NAME = 'attribute_set_name';
    const FIELD_ATTRIBUTE_SET_GROUP = 'group';
    const FIELD_ATTRIBUTE_SET_ATTRIBUTE = 'attribute';
    const FIELD_ATTRIBUTE_SET_SORT_ORDER = 'attribute_set_sort_order';
    const FIELD_ATTRIBUTE_SORT_ORDER = 'attribute_sort_order';
    const DEFAULT_ATTRIBUTE_SET_ID = 4;
    const GROUP_SORT_ORDER = 100;
    const NAMESPACE_PRODUCT_LISTING = 'product_listing';

    /**
     * @var SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var Collection
     */
    protected $attributeSetCollectionFactory;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    protected $groupMap = [
        'General' => 'Product Details'
    ];

    public function __construct(
        Csv $fileCsv,
        Config $configHelper,
        SetFactory $attributeSetFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        EavSetupFactory $eavSetupFactory,
        GroupFactory $groupFactory,
        AttributeRepositoryInterface $attributeRepository,
        BookmarkRepositoryInterface $bookmarkRepository,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->groupFactory = $groupFactory;
        $this->attributeRepository = $attributeRepository;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        if (isset($row[self::FIELD_ATTRIBUTE_SET_NAME])) {
            $attributeSetName = trim($row[self::FIELD_ATTRIBUTE_SET_NAME]);
            $attributeSetSortOrder = trim($row[self::FIELD_ATTRIBUTE_SET_SORT_ORDER]);
            $groupName = trim($row[self::FIELD_ATTRIBUTE_SET_GROUP]);
            $attributeCode = trim($row[self::FIELD_ATTRIBUTE_SET_ATTRIBUTE]);
            $attributeSortOrder = trim($row[self::FIELD_ATTRIBUTE_SORT_ORDER]);
            /* Check if the attribute exists */
            try {
                $attribute = $this->attributeRepository->get(self::CATALOG_ENTITY_TYPE, $attributeCode);
                /* Create attribute set if it does not exist */
                $attributeSetCollection = $this->attributeSetCollectionFactory->create();
                $attributeSetCollection
                    ->addFieldToFilter('attribute_set_name', $attributeSetName)
                    ->addFieldToFilter('entity_type_id', self::CATALOG_ENTITY_TYPE)
                    ->load();
                $attributeSet = $attributeSetCollection->getFirstItem();

                if (!$attributeSet->getId()) {
                    $message = __METHOD__ . ': ' . __LINE__ . ': Attribute set  ' . $attributeSetName . ' doesn\'t exist. Creating it ...';
                    echo $message . PHP_EOL;
                    $this->log(LogLevel::INFO, $message);
                    $attributeSet = $this->attributeSetFactory->create();
                    $attributeSet->setAttributeSetName($attributeSetName);
                    $attributeSet->setEntityTypeId(self::CATALOG_ENTITY_TYPE);
                    $attributeSet->setSortOrder($attributeSetSortOrder);
                    $attributeSet->validate();
                    $attributeSet->save();
                    $attributeSet->initFromSkeleton(self::DEFAULT_ATTRIBUTE_SET_ID);
                    $attributeSet->save();
                    sleep(2);
                }

                $attributeSetAttributes = $this->attributeCollectionFactory->create();
                $attributeSetAttributes = $attributeSetAttributes->setAttributeSetFilter($attributeSet->getAttributeSetId());
                $attributeItems = $attributeSetAttributes->getItems();

                $message = __METHOD__ . ': ' . __LINE__ . ': Adding attribute ' . $attributeCode . ' to attribute set ' . $attributeSetName . ' ...';
                echo $message . PHP_EOL;
                $this->log(LogLevel::INFO, $message);

                foreach ($attributeItems as $item) {
                    if ($item->getAttributeCode() == $attributeCode) {
                        $message = __METHOD__ . ': ' . __LINE__ . ': ' . $attributeCode . ' is already in attribute set ' . $attributeSetName . '. Skipping...';
                        echo $message . PHP_EOL;
                        $this->log(LogLevel::INFO, $message);
                        return;
                    }
                }

                $message = __METHOD__ . ': ' . __LINE__ . ': Adding attribute ' . $attributeCode . ' to group ' . $groupName . ' within ' . $attributeSetName;
                echo $message . PHP_EOL;
                $this->log(LogLevel::INFO, $message);

                /* Check if group exists */
                if (isset($this->groupMap[$groupName])) {
                    $groupName = $this->groupMap[$groupName];
                }
                $groupCollection = $this->groupCollectionFactory->create();
                $groupCollection
                    ->addFieldToFilter('attribute_group_name', $groupName)
                    ->addFieldToFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                    ->load();
                $group = $groupCollection->getFirstItem();
                if (!$group->getId()) {
                    $message =  __METHOD__ . ': ' . __LINE__ . ': Group ' . $groupName . ' does not exist. Creating...';
                    echo $message . PHP_EOL;
                    $this->log(LogLevel::INFO, $message);
                    $group = $this->groupFactory->create();
                    $group->setAttributeGroupName($groupName);
                    $group->setAttributeSetId($attributeSet->getId());
                    $group->setSortOrder(self::GROUP_SORT_ORDER);

                    try {
                        $group->save();
                        sleep(2);
                    } catch (\Exception $e) {
                        $message =  __METHOD__ . ': ' . __LINE__ . ': Error while saving group: ' . $e->getMessage();
                        echo $message . PHP_EOL;
                        $this->log(LogLevel::INFO, $message);
                    }
                }

                $setup = $this->eavSetupFactory->create();
                $setup->addAttributeToGroup(self::CATALOG_ENTITY_TYPE, $attributeSet->getAttributeSetId(), $group->getAttributeGroupId(), $attribute->getAttributeId(), $attributeSortOrder);
            } catch (NoSuchEntityException $e) {
                $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Skipping:' . $e->getMessage();
                echo $errorMessage . PHP_EOL;
                $this->log(LogLevel::ERROR, $errorMessage);
            } catch (StateException $e) {
                $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Skipping attribute set ' . $attributeSetName . ': ' . $e->getMessage();
                echo $errorMessage . PHP_EOL;
                $this->log(LogLevel::ERROR, $errorMessage);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleUpdate(array $row)
    {
        if (isset($row[self::FIELD_ATTRIBUTE_SET_NAME])) {
            $attributeSetName = trim($row[self::FIELD_ATTRIBUTE_SET_NAME]);
            $attributeSetSortOrder = trim($row[self::FIELD_ATTRIBUTE_SET_SORT_ORDER]);
            $groupName = trim($row[self::FIELD_ATTRIBUTE_SET_GROUP]);
            $attributeCode = trim($row[self::FIELD_ATTRIBUTE_SET_ATTRIBUTE]);
            $attributeSortOrder = trim($row[self::FIELD_ATTRIBUTE_SORT_ORDER]);
            /* Check if the attribute exists */
            try {
                $attribute = $this->attributeRepository->get(self::CATALOG_ENTITY_TYPE, $attributeCode);
                /* Create attribute set if it does not exist */
                $attributeSetCollection = $this->attributeSetCollectionFactory->create();
                $attributeSetCollection
                    ->addFieldToFilter('attribute_set_name', $attributeSetName)
                    ->addFieldToFilter('entity_type_id', self::CATALOG_ENTITY_TYPE)
                    ->load();
                $attributeSet = $attributeSetCollection->getFirstItem();

                if (!$attributeSet->getId()) {
                    $message = __METHOD__ . ': ' . __LINE__ . ': Attribute set  ' . $attributeSetName . ' doesn\'t exist. Creating it ...';
                    echo $message . PHP_EOL;
                    $this->log(LogLevel::INFO, $message);
                    $attributeSet = $this->attributeSetFactory->create();
                    $attributeSet->setAttributeSetName($attributeSetName);
                    $attributeSet->setEntityTypeId(self::CATALOG_ENTITY_TYPE);
                    $attributeSet->setSortOrder($attributeSetSortOrder);
                    $attributeSet->validate();
                    $attributeSet->save();
                    $attributeSet->initFromSkeleton(self::DEFAULT_ATTRIBUTE_SET_ID);
                    $attributeSet->save();
                    sleep(2);
                }

                $attributeSetAttributes = $this->attributeCollectionFactory->create();
                $attributeSetAttributes = $attributeSetAttributes->setAttributeSetFilter($attributeSet->getAttributeSetId());
                $attributeItems = $attributeSetAttributes->getItems();

                $message = __METHOD__ . ': ' . __LINE__ . ': Adding attribute ' . $attributeCode . ' to attribute set ' . $attributeSetName . ' ...';
                echo $message . PHP_EOL;
                $this->log(LogLevel::INFO, $message);

                foreach ($attributeItems as $item) {
                    if ($item->getAttributeCode() == $attributeCode) {
                        $message = __METHOD__ . ': ' . __LINE__ . ': ' . $attributeCode . ' is already in attribute set ' . $attributeSetName . '. Skipping...';
                        echo $message . PHP_EOL;
                        $this->log(LogLevel::INFO, $message);
                        return;
                    }
                }

                $message = __METHOD__ . ': ' . __LINE__ . ': Adding attribute ' . $attributeCode . ' to group ' . $groupName . ' within ' . $attributeSetName;
                echo $message . PHP_EOL;
                $this->log(LogLevel::INFO, $message);

                /* Check if group exists */
                if (isset($this->groupMap[$groupName])) {
                    $groupName = $this->groupMap[$groupName];
                }
                $groupCollection = $this->groupCollectionFactory->create();
                $groupCollection
                    ->addFieldToFilter('attribute_group_name', $groupName)
                    ->addFieldToFilter('attribute_set_id', $attributeSet->getAttributeSetId())
                    ->load();
                $group = $groupCollection->getFirstItem();
                if (!$group->getId()) {
                    $message =  __METHOD__ . ': ' . __LINE__ . ': Group ' . $groupName . ' does not exist. Creating...';
                    echo $message . PHP_EOL;
                    $this->log(LogLevel::INFO, $message);
                    $group = $this->groupFactory->create();
                    $group->setAttributeGroupName($groupName);
                    $group->setAttributeSetId($attributeSet->getId());
                    $group->setSortOrder(self::GROUP_SORT_ORDER);

                    try {
                        $group->save();
                        sleep(2);
                    } catch (\Exception $e) {
                        $message =  __METHOD__ . ': ' . __LINE__ . ': Error while saving group: ' . $e->getMessage();
                        echo $message . PHP_EOL;
                        $this->log(LogLevel::INFO, $message);
                    }
                }

                $setup = $this->eavSetupFactory->create();
                $setup->addAttributeToGroup(self::CATALOG_ENTITY_TYPE, $attributeSet->getAttributeSetId(), $group->getAttributeGroupId(), $attribute->getAttributeId(), $attributeSortOrder);
            } catch (NoSuchEntityException $e) {
                $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Skipping:' . $e->getMessage();
                echo $errorMessage . PHP_EOL;
                $this->log(LogLevel::ERROR, $errorMessage);
            } catch (StateException $e) {
                $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Skipping attribute set ' . $attributeSetName . ': ' . $e->getMessage();
                echo $errorMessage . PHP_EOL;
                $this->log(LogLevel::ERROR, $errorMessage);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function processAfter()
    {
        $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder
                    ->setField('namespace')
                    ->setConditionType('eq')
                    ->setValue(self::NAMESPACE_PRODUCT_LISTING)
                    ->create(),
            ]
        );

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->bookmarkRepository->getList($searchCriteria);

        if ($searchResults->getTotalCount()) {
            foreach ($searchResults->getItems() as $bookmark) {
                try {
                    $this->bookmarkRepository->delete($bookmark);
                } catch (CouldNotDeleteException $e) {
                    $errorMessage = __METHOD__ . ': ' . __LINE__ . ': Could not delete bookmark:' . $e->getMessage();
                    echo $errorMessage . PHP_EOL;
                    $this->configHelper->log(LogLevel::ERROR, $errorMessage);
                }
            }
        }

        $infoMessage = __METHOD__ . ': ' . __LINE__ . ': UI Bookmarks have been cleared.';
        echo $infoMessage . PHP_EOL;
        $this->log(LogLevel::INFO, $infoMessage);
    }
}