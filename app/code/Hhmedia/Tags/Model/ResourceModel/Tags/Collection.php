<?php

/**
 * Tags Resource Collection
 */
namespace Hhmedia\Tags\Model\ResourceModel\Tags;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Tags\Model\Tags', 'Hhmedia\Tags\Model\ResourceModel\Tags');
    }
}
