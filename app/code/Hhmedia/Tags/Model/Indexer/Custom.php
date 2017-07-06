<?php
namespace Hhmedia\Tags\Model\Indexer;

/**
 * Class Custom
 * @package Hhmedia\Tags\Model\Indexer
 */
class Custom implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $manager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Custom constructor.
     * @param \Magento\Framework\Event\ManagerInterface $manager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $manager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    )
    {
        $this->manager = $manager;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute($ids) {}

    /**
     * @inheritdoc
     */
    public function executeFull()
    {
        $products = $this->collectionFactory->create();
        $products->addAttributeToSelect('product_tags');

        foreach ($products as $product) {
            $this->manager->dispatch('catalog_product_save_after',  ['product' => $product]);
        }
    }

    /**
     * @inheritdoc
     */
    public function executeList(array $ids)
    {

    }

    /**
     * @inheritdoc
     */
    public function executeRow($id)
    {

    }
}