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
namespace Lof\Faq\Block\Widget;

class Faq extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Lof\Faq\Helper\Data
     */
    protected $_faqHelper;

    /**
     * @var \Lof\Faq\Model\Question
     */
    protected $_questionFactory;

    /**
     * @var \Lof\Faq\Model\ResourceModel\Question\Collection
     */
    protected $_collection;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resource;

    /**
     * @param \Magento\Framework\View\Element\Template\Context
     * @param \Magento\Framework\Registry
     * @param \Lof\Faq\Model\Question
     * @param \Lof\Faq\Helper\Data
     * @param \Magento\Framework\App\ResourceConnection
     * @param array
     */
    public function __construct(
    	\Magento\Framework\View\Element\Template\Context $context,
    	\Magento\Framework\Registry $registry,
    	\Lof\Faq\Model\Question $questionFactory,
    	\Lof\Faq\Helper\Data $faqHelper,
      \Magento\Framework\App\ResourceConnection $resource,
      array $data = []
      ) {
    	$this->_faqHelper = $faqHelper;
    	$this->_questionFactory = $questionFactory;
      $this->_resource = $resource;
      parent::__construct($context);
      $this->setTemplate("Lof_Faq::widget/list.phtml");
    }

    public function _toHtml(){
    	if(!$this->_faqHelper->getConfig('general_settings/enable')) return;
    	$store = $this->_storeManager->getStore();
    	$itemsperpage = (int)$this->getData('item_per_page');
      $categories = $this->getData('categories');
      $cats = explode(',', $categories);

      $questionCollection = $this->_questionFactory->getCollection()
      ->addFieldToFilter('main_table.is_active',1);

      if($this->getData('is_featured')){
        $questionCollection->addFieldToFilter('main_table.is_featured',1);
      }

      if($itemsperpage ) $questionCollection->setPageSize($itemsperpage);
      $questionCollection->addStoreFilter($store);
      if(count($cats)>0){
        $questionCollection->getSelect("main_table.question_id")
        ->joinLeft([
          'cat' => $this->_resource->getTableName('lof_faq_question_category')],
          'cat.question_id = main_table.question_id',[
          "question_id" => "question_id"
          ])->where('cat.category_id IN (?)', implode($cats, ','));
        if(count($cats)==1){
          $questionCollection->getSelect()->order('position ASC');
        }
        $questionCollection->getSelect()->order("main_table.question_id DESC")->group('cat.question_id');   
      }
      $this->setCollection($questionCollection);

      return parent::_toHtml();
    }

    public function setCollection($collection)
    {
    	$this->_collection = $collection;
    	return $this;
    }

    public function getCollection(){
    	return $this->_collection;
    }
  }