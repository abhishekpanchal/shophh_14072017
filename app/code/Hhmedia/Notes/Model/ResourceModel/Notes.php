<?php

namespace Hhmedia\Notes\Model\ResourceModel;

/**
 * Notes Resource Model
 */
class Notes extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('hhmedia_notes', 'notes_id');
    }
}
