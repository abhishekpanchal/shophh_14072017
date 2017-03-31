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
    const TBL_ATT_PRODUCT = 'hhmedia_tags_product';

    protected function _construct()
    {
        $this->_init('hhmedia_tags', 'tags_id');
    }
}
