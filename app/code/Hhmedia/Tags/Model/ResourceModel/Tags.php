<?php

namespace Hhmedia\Tags\Model\ResourceModel;

/**
 * Tags Resource Model
 */
class Tags extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('hhmedia_tags', 'tags_id');
    }
}
