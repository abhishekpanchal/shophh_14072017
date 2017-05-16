<?php

/**
 * Notes Resource Collection
 */
namespace Hhmedia\Notes\Model\ResourceModel\Notes;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Notes\Model\Notes', 'Hhmedia\Notes\Model\ResourceModel\Notes');
    }
}
