<?php
namespace Hhmedia\Tags\Observer;

use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Productsaveafter
 * @package Hhmedia\Tags\Observer
 */
class Productsaveafter implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Productsaveafter constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    )
    {
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $productTags = 	$observer->getEvent()->getProduct()->getData('product_tags');
        $productId = $observer->getEvent()->getProduct()->getId();

        if(isset($productTags)){
        	try {
			    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			    $tagCollection = $objectManager->create('Hhmedia\Tags\Model\Tags');

			    $oldTags = (array)$tagCollection->getTags($productId);
			    $newTags = explode(",",$productTags);

			    $this->_resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
		        $connection = $this->_resources->getConnection();
			    $table = $this->_resources->getTableName(\Hhmedia\Tags\Model\ResourceModel\Tags::TBL_ATT_PRODUCT);
		        $insert = array_diff($newTags, $oldTags);
		        $delete = array_diff($oldTags, $newTags);

		        if ($delete) {
		            $where = ['product_id = ?' => (int)$productId, 'tags_id IN (?)' => $delete];
		            $connection->delete($table, $where);
		        }

		        if ($insert) {
		            $data = [];
		            foreach ($insert as $tag_id) {
		                $data[] = ['product_id' => (int)$productId, 'tags_id' => (int)$tag_id];
		            }
		            $connection->insertMultiple($table, $data);
		        }
            } catch (\Exception $e) {
                $this->logger->error('Product tags could not save: ' . $e->getMessage());
            }
	    }
    }
}