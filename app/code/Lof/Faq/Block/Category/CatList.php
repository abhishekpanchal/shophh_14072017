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
namespace Lof\Faq\Block\Category;

class CatList extends \Magento\Framework\View\Element\Template
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
	protected $_coreRegistry = null;

    /**
     * @var \Lof\Faq\Helper\Category
     */
    protected $_faqHelper;

    /**
     * @var \Lof\Faq\Model\Question
     */
    protected $_questionFactory;

    /**
     * @var \Lof\Faq\Model\Category
     */
    protected $_categoryFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Lof\Faq\Model\Question
     * @param \Lof\Faq\Model\Category
     * @param \Lof\Faq\Helper\Data
     * @param \Magento\Framework\App\ResourceConnection
     * @param array
     */
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
    	\Magento\Framework\Registry $registry,
    	\Lof\Faq\Model\Question $questionFactory,
        \Lof\Faq\Model\Category $categoryFactory,
        \Lof\Faq\Helper\Data $faqHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
        ) {
    	$this->_faqHelper = $faqHelper;
    	$this->_coreRegistry = $registry;
    	$this->_questionFactory = $questionFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_resource = $resource;
        parent::__construct($context);
    }

    public function getCategoryCollection(){
        $store = $this->_storeManager->getStore();
        $categoryCollection = $this->_categoryFactory->getCollection()
        ->addFieldToFilter('is_active',1)
        ->addFieldToFilter('include_in_sidebar',1)
        ->addStoreFilter($store)
        ->setCurPage(1);
        $categoryCollection->getSelect()->order('position ASC');
        return $categoryCollection;
    }

}