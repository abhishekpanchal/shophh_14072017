<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * http://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Lof_FAQ
 * @copyright  Copyright (c) 2016 Landofcoder (http://www.landofcoder.com/)
 * @license    http://www.landofcoder.com/LICENSE-1.0.html
 */
namespace Lof\Faq\Model;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Question extends \Magento\Framework\Model\AbstractModel
{	
	/**
     * Question's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * URL Model instance
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    protected $_resource;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Model\Context
     * @param \Magento\Framework\Registry
     * @param \Lof\Faq\Model\ResourceModel\Question|null
     * @param \Lof\Faq\Model\ResourceModel\Question\Collection|null
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Framework\UrlInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface
     * @param array
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Lof\Faq\Model\ResourceModel\Question $resource = null,
        \Lof\Faq\Model\ResourceModel\Question\Collection $resourceCollection = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
        ) {
        $this->_storeManager = $storeManager;
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_resource = $resource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\Faq\Model\ResourceModel\Question');
    }
    
    public function getQuestionCategories(){
        $connection = $this->_resource->getConnection();
        $select = 'SELECT * FROM ' . $this->_resource->getTable('lof_faq_question_category') . ' WHERE question_id = ' . $this->getData("question_id");
        $categories = $connection->fetchAll($select);
        $tmp = [];
        foreach ($categories as $k => $v) {
            $select = 'SELECT * FROM ' . $this->_resource->getTable('lof_faq_category') . ' WHERE category_id = ' . $v['category_id'];
            $select = $connection->select()->from(['lof_faq_category' => $this->_resource->getTable('lof_faq_category')])
            ->where('lof_faq_category.category_id = ' . (int)$v['category_id'])
            ->order('lof_faq_category.position DESC');
            $category = $connection->fetchRow($select);
            $tmp[] = $category;
        }
        return $tmp;
    }
}
