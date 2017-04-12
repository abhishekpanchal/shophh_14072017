<?php

namespace Hhmedia\Collection\Model\ResourceModel;

/**
 * Collection Resource Model
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('hhmedia_collection', 'collection_id');
    }
}
