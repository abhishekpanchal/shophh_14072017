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
namespace Lof\Faq\Model\ResourceModel\Question;

use \Lof\Faq\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'question_id';

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performAfterLoad('lof_faq_question_store', 'question_id');

        return parent::_afterLoad();
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Lof\Faq\Model\Question', 'Lof\Faq\Model\ResourceModel\Question');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Returns pairs question_id - title
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('question_id', 'title');
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);

        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }
        if (!is_array($store)) {
            $store = [$store];
        }
        /*$this->getSelect()->join(
                ['store_table' => $this->getTable('lof_faq_question_store')],
                'main_table.question_id = store_table.question_id',
                []
            )->where('store_table.store_id in (?,0)', $store)
        ->group(
                'main_table.question_id'
            );*/
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable('lof_faq_question_store', 'question_id');
    }
}